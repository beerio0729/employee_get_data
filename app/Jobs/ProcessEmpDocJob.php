<?php

namespace App\Jobs;

use Exception;
use Throwable;
use RuntimeException;
use Illuminate\Bus\Queueable;
use App\Events\ProcessEmpDocEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;

/**
 * Job สำหรับประมวลผล Resume Text ที่ดึงมาจาก PDF ผ่าน Gemini API ใน Background
 */
class ProcessEmpDocJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $name_file;
    protected $filePaths;
    public $hasOneData = [];
    public $hasManyData = [];
    public $user;

    public int $tries = 2;
    // // กำหนดเวลาที่ใช้ในการรัน Job (ถ้าเกิน 180s Job จะถูกยกเลิกและพยายามใหม่)
    public int $timeout = 180;
    public $dontReport = [\RuntimeException::class];

    /**
     * สร้าง Job Instance ใหม่
     */
    public function __construct(array $data, $user)
    {
        $this->name_file = array_keys($data)[0]; //ถอด key ของ array จะได้ชนิดเอกสาร
        $this->filePaths = $data[$this->name_file];
        $this->user = $user;
        //dump($this->name_file);
    }

    /**
     * เมธอดนี้จะถูกเรียกเมื่อ Worker ดึง Job ออกจากคิว
     */
    public function handle(): void
    {
        event(new ProcessEmpDocEvent('กำลังเตรียมข้อมูล...', $this->user));
        if (is_array($this->filePaths)) {
            // Case 1: Multiple Files (Array)
            $contents = $this->buildMultiContents($this->filePaths);
        } else {
            // Case 2: Single File (String)
            // เราส่ง $this->filePaths เข้าไปตรงๆ ซึ่งตอนนี้คือ String
            $contents = $this->buildContents($this->filePaths);
        }

        event(new ProcessEmpDocEvent('เตรียมข้อมูลเสร็จแล้ว Ai กำลังประมวลผล...', $this->user));
        $this->sendJsonToAi($contents);

        if ($this->hasOneData['check'] === 'yes') {
            $this->processSaveToDB($this->hasOneData, $this->hasManyData);
            event(new ProcessEmpDocEvent('กระบวนการเสร็จสิ้น', $this->user, 'close'));
        } else {

            $doc_file = $this->user->userHasmanyDocEmp()
                ->where('file_name', $this->name_file)
                ->first();

            if ($doc_file) {
                Storage::disk('public')->delete($doc_file->path);
                $doc_file->delete();
            }
            // 2. โยน Exception เพื่อสั่งให้ Job Worker จัดการ
            $this->fail('ขออภัย! คุณอับโหลดเอกสารผิดประเภท โปรดอับโหลดเอกสารตามประเภทที่ระบุ');
        }
    }

    public function failed(?Throwable $exception): void
    {   //dump($exception->getMessage());
        event(new ProcessEmpDocEvent(
            $exception->getMessage() ?? 'ขออภัย! เกิดข้อผิดพลาดโปรดลองใหม่อีกครั้ง',
            $this->user,
            'close' // error
        ));
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    protected function buildContents($filePaths)
    {

        $fileContent = Storage::disk('public')->get($filePaths);
        $mimeType = Storage::disk('public')->mimeType($filePaths);
        //dump($mimeType);
        $parts = [
            [
                'text' => config("empPromtForAi.{$this->name_file}", [])
            ],
            [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => base64_encode($fileContent)
                ]
            ]
        ];

        $contents =
            [
                'role' => 'user',
                'parts' => $parts,
            ];

        return $contents;
    }

    protected function buildMultiContents(array $filePaths): array
    {
        // Logic ที่เราสร้างขึ้นสำหรับ Array และ Loop
        $parts = [
            [
                'text' => config("empPromtForAi.{$this->name_file}", [])
            ]
        ];

        foreach ($filePaths as $filePath) {
            $fileContent = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath);

            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => base64_encode($fileContent)
                ]
            ];
        }

        return [
            'role' => 'user',
            'parts' => $parts,
        ];
    }
    protected function sendJsonToAi($contents): void
    {

        // 1. กำหนด API Key และ URL
        $apiKey = env('GEMINI_API_KEY');
        $model = 'gemini-2.5-flash';
        $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
        $url = "{$baseUrl}{$model}:generateContent?key={$apiKey}";

        // 2. กำหนด JSON Schema (ใช้ Schema เดิมจาก Controller)
        $arrayForSchema = config("empSendtoAIArray.{$this->name_file}", []);
        $jsonSchema = [
            'type' => 'object',
            'properties' => $arrayForSchema,
        ];

        // 3. กำหนด Payload (Body)
        $payload = [
            'contents' => $contents,

            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
                'responseSchema' => $jsonSchema
            ],
            'systemInstruction' => [
                'parts' => [[
                    'text' => '
                        Respond ONLY with a valid JSON object. 
                        Do not add any introductory or concluding text, notes, or markdown formatting (e.g., ```json). 
                        The JSON structure and field names must strictly follow the given schema — do not omit any field. 
                        If any value is missing or unknown, explicitly set it to null.
                    '
                ]]
            ],

        ];
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(120) // Time out 120s 
                ->connectTimeout(20) // Connect timeout 20s
                ->withOptions([
                    'curl' => [
                        // 🌟 ตั้งค่า Buffer Size ให้ใหญ่เป็นพิเศษ (512KB) เพื่อแก้ปัญหา JSON ถูกตัดขาด
                        CURLOPT_BUFFERSIZE => 524288,
                    ],
                ])->post($url, $payload);
            if (!$response->successful()) {
                Log::channel('gemini')->debug("API มีปัญหาซะแล้ว: " . $response->status(), $response->json());
                // ถ้าเกิด Error ให้ throw Exception เพื่อให้ Job ถูก Retry
                throw new \Exception('การประมวลผลของ Ai ไม่สำเร็จกำลังลองใหม่อีกครั้ง...');
                //event(new ProcessEmpDocEvent('การประมวลผลของ Ai ไม่สำเร็จกำลังลองใหม่อีกครั้ง...', $this->user));
            }
        } catch (ConnectionException $e) {
            // 💥 1. ดักจับ: Timeout หรือ Connection Error 💥
            Log::channel('gemini')->error("Connection/Timeout Error: " . $e->getMessage());

            // โยน Exception เพื่อให้ Job ถูก Retry
            throw new \Exception('การเชื่อมต่อกับ AI ล้มเหลว โปรดลองใหม่อีกครั้ง');
        } catch (\Throwable $e) {
            throw new \Exception('มีปัญหาที่ไม่คาดคิดเกี่ยวกับ Ai ไม่สำเร็จกำลังลองใหม่อีกครั้ง...');
            // หากเป็น Exception ที่ไม่เกี่ยวข้องกับการเชื่อมต่อโดยตรง (เช่น PHP Error)
            Log::channel('gemini')->error("Uncaught Error in API Call: " . $e->getMessage());
            throw $e;
        }

        $this->jsonToArray($response);
    }

    // 5. ดึงผลลัพธ์ JSON (ซึ่งตอนนี้ควรเป็น JSON string ที่สะอาด)
    public function jsonToArray($response)
    {
        $result = $response->json();
        $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        try {
            // 1. พยายามถอดรหัส JSON
            $finalJsonArray = json_decode($generatedText, true);
            if (json_last_error() !== 0) {
                // Log และ Throw Exception เพื่อสั่ง Retry
                throw new \RuntimeException("Ai ประมวลผล และส่งข้อมูลกลับมาผิดพลาด");
            }

            // 3. ตรวจสอบผลลัพธ์ว่าถูกต้องหรือไม่
            if (is_array($finalJsonArray)) {

                // 3.1. JSON ถูกต้องและเป็น Array: ดำเนินการ Sorting
                $this->hasOneData = $this->SortingArray($finalJsonArray);
            } else {

                // 3.2. JSON Decode สำเร็จ แต่ผลลัพธ์ไม่ใช่ Array: จัดการข้อผิดพลาดตาม Flow เดิม
                // (ส่วนนี้อาจทำให้เกิดปัญหาถ้า $this->hasOneData ถูกใช้ต่อโดยไม่มีการตรวจสอบ)
                $this->hasOneData = [];
                $this->hasManyData = [];
            }
        } catch (RuntimeException $e) {

            // ⚠️ ดักจับ RuntimeException ที่เราโยนเองเมื่อเกิด JSON Decode Error หรือโครงสร้างข้อมูลผิดพลาด

            // ต้อง throw ซ้ำ เพื่อส่ง Exception ต่อไปให้ handle() และ Job Worker จัดการ
            throw $e;
        } catch (Throwable $e) {

            // 💥 ดักจับ Exception/Error อื่นๆ ที่ไม่คาดคิดในการประมวลผล (เช่น Memory issue)

            Log::channel('gemini')->error("ข้อผิดพลาดร้ายแรงในฟังก์ชันประมวลผล JSON: ");

            // ต้อง throw ซ้ำ เพื่อส่ง Exception ต่อไปให้ handle() และ Job Worker จัดการ
            throw $e;
        }
    }


    /**
     * ฟังก์ชันหลักที่วนซ้ำใน Array ทั้งหมดเพื่อทำความสะอาดทุกคีย์/ค่า
     * ฟังก์ชันนี้รับผิดชอบในการเข้าถึงข้อมูลทุกระดับ
     */
    protected function SortingArray(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // ถ้าเป็น Array ให้เรียกตัวเองซ้ำ (Recursion)
                $this->hasManyData[$key] = $this->cleanHasMany($value);
            } else {
                // ถ้าเป็นค่าเดี่ยว ให้เรียกฟังก์ชันทำความสะอาด
                $result[$key] = $this->cleanArrayFormAi($value);
            }
        }
        return $result;
    }

    protected function cleanHasMany(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // ถ้ามี array ซ้อนอีกชั้น (เช่น many ของ many)
                $result[$key] = $this->cleanHasMany($value);
            } else {
                $result[$key] = $this->cleanArrayFormAi($value);
            }
        }
        return $result;
    }

    /**
     * ตรวจสอบและแปลงค่าที่ว่างเปล่า ("" หรือ "null") ให้เป็นค่า NULL
     * ฟังก์ชันนี้รับผิดชอบเฉพาะการทำความสะอาดค่าแต่ละตัว
     */
    protected function cleanArrayFormAi($value)
    {
        // ถ้าเป็น NULL อยู่แล้ว หรือไม่ใช่ String (เช่น เป็นตัวเลข, Boolean) ให้ส่งค่าเดิมกลับไป
        if ($value === null || !is_string($value)) {
            return $value;
        }

        $trimmedValue = trim($value);

        // แก้ไข: "null", "NULL" (ไม่คำนึงถึงพิมพ์เล็กพิมพ์ใหญ่)
        if (strtolower($trimmedValue) === 'null') {
            return null;
        }

        // แก้ไข: "" (Empty String) และ "   " (Whitespace)
        if ($trimmedValue === '') {
            return null;
        }

        return $value;
    }


    public function processSaveToDB(array $hasOneData, array $hasManyData): void
    {   
        dump($hasOneData);
        return;
        // $className = 'App\\Services\\JobForSaveDBFromAI\\Save' . ucfirst($this->name_file) . 'ToDB';
        // $instance = new $className();
        // $instance->saveToDB($hasOneData, $hasManyData, $this->user);
    }
}
