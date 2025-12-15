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

class ProcessNoJsonEmpDocJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file_name;
    protected $file_name_th;
    protected $file_Paths;
    protected $success_msg; //ข้อความที่ return มาจาก Savetodb
    public $hasManyData = [];
    public $user;

    public int $tries = 2;
    // // กำหนดเวลาที่ใช้ในการรัน Job (ถ้าเกิน 180s Job จะถูกยกเลิกและพยายามใหม่)
    public int $timeout = 180;
    public $dontReport = [\RuntimeException::class];

    /**********************************************/

    public function __construct(array $data, $user, $file_name, $file_name_th)
    {   
        $this->file_name = $file_name;
        $this->file_name_th = $file_name_th;
        $this->file_Paths = $data;
        $this->user = $user;
        event(new ProcessEmpDocEvent('กำลังเตรียมข้อมูล...', $this->user));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $contents = $this->buildMultiContents();

        event(new ProcessEmpDocEvent('เตรียมข้อมูลเสร็จแล้ว Ai กำลังประมวลผล...', $this->user));
        $this->sendJsonToAi($contents);

        $this->processSaveToDB();
        event(new ProcessEmpDocEvent($this->success_msg, $this->user, 'close', $this->file_name, true));
    }

    public function failed(?Throwable $exception): void
    {   //dump($exception->getMessage());
        event(new ProcessEmpDocEvent(
            $exception->getMessage() ?? 'ขออภัย! เกิดข้อผิดพลาดโปรดลองใหม่อีกครั้ง',
            $this->user,
            'close',
            $this->file_name,
            false

        ));
        $this->deleteFile();
    }

    protected function buildMultiContents(): array
    {   
        $parts = [
            [
                'text' => config("empPromtForAi.{$this->file_name}", [])
            ]
        ];

        foreach ($this->file_Paths as $filePath) {
            $fileContent = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath);
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => base64_encode($fileContent)
                ]
            ];
        }
        //dump($parts);
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

        // 3. กำหนด Payload (Body)
        $payload = [
            'contents' => $contents,

            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
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
                throw new \Exception('การประมวลผลของ Ai ไม่สำเร็จ<br>กำลังลองใหม่อีกครั้ง...');
                //event(new ProcessEmpDocEvent('การประมวลผลของ Ai ไม่สำเร็จกำลังลองใหม่อีกครั้ง...', $this->user));
            }
        } catch (ConnectionException $e) {
            // 💥 1. ดักจับ: Timeout หรือ Connection Error 💥
            Log::channel('gemini')->error("Connection/Timeout Error: " . $e->getMessage());

            // โยน Exception เพื่อให้ Job ถูก Retry
            throw new \Exception('การเชื่อมต่อกับ AI ล้มเหลว<br>โปรดลองใหม่อีกครั้ง');
        } catch (\Throwable $e) {
            throw new \Exception('มีปัญหาที่ไม่คาดคิดเกี่ยวกับ Ai<br>กำลังลองใหม่อีกครั้ง...');
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
                $this->hasManyData = $this->cleanArrayFormAi($finalJsonArray);
            } else {
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

    public function processSaveToDB(): void
    {   
        $className = 'App\\Services\\JobForSaveDBFromAI\\Save' . ucfirst($this->file_name) . 'ToDB';
        $instance = new $className();
        $msg = $instance->saveToDB($this->hasManyData, $this->user, $this->file_Paths, $this->file_name);
        $this->success_msg = $msg;
    }

}
