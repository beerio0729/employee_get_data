<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveCertificateToDB {
    public function saveToDB(array $hasManyData, $user, array $path, $file_name): string
    {   
        $user->userHasoneCertificate()->updateOrCreate(
            ['user_id' => $user->id],
            ['data' => $hasManyData]
        );
        
        return 'กระบวนการเสร็จสิ้น<br>โปรดตรวจสอบข้อมูลโดยละเอียดอีกครั้ง ';
    }
}