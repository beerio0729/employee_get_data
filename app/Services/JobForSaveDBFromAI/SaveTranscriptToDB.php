<?php

namespace App\Services\JobForSaveDBFromAI;

use Illuminate\Support\Facades\Storage;

class SaveTranscriptToDB
{
    public function saveToDB(array $hasManyData, $user, array $path, $file_name): string
    {
        $notTranscriptCount = 0; // ตัวนับฝั่ง else
        if (!empty($hasManyData)) {
            foreach ($hasManyData as $index => $item) {
                if ($item['check'] === 'yes') {
                    $user->userHasmanyTranscript()->create([
                        'prefix_name' => $item['prefix_name'],
                        'name' => $item['name'],
                        'last_name' => $item['last_name'],
                        'institution' => $item['institution'],
                        'degree' => $item['degree'],
                        'education_level' => $item['education_level'],
                        'faculty' => $item['faculty'],
                        'major' => $item['major'],
                        'minor' => $item['minor'],
                        'date_of_admission' => $item['date_of_admission'],
                        'date_of_graduation' => $item['date_of_graduation'],
                        'gpa' => $item['gpa'],
                        "file_path" => $path[$index],
                    ]);
                } else {
                    $notTranscriptCount++; // 👈 นับตรงนี้
                    $this->deleteFile($user, $path[$index], $file_name);
                }
            }
        }

        
        
        if ($notTranscriptCount > 0) {
            $message = "กระบวนการเสร็จสิ้น<br>มี {$notTranscriptCount} เอกสารที่ไม่ใช่ \"วุฒิการศึกษา\"<br>
            ระบบได้ลบเอกสารเหล่านั้นออกไปแล้ว<br>โปรดตรวจสอบข้อมูลโดยละเอียดอีกครั้ง ";
        } else {
            $message = 'กระบวนการเสร็จสิ้น<br>โปรดตรวจสอบข้อมูลโดยละเอียดอีกครั้ง ';
        }

        return $message;
    }

    public function deleteFile($user, $path_in_loop, $file_name)
    {
        $doc = $user->userHasmanyDocEmp()
            ->where('file_name', $file_name)
            ->first();
        $path = $doc->path;
        Storage::disk('public')->delete($path_in_loop); //ลบไฟล์นั้น
        if (count($path) > 1) {
            $key = array_search($path_in_loop, $path, true);
            dump($path_in_loop);
            dump($path);
            unset($path[$key]);
            dump($path);
            $user->userHasmanyDocEmp()->updateOrCreate(
                ['file_name' => $file_name],
                ['path' => $path]
            );
        } else {
            $doc->delete();
        }
    }
}
