<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveCertificateToDB {
    public function saveToDB(array $hasOneData, array $hasManyData, $user): void
    {   
        dump($hasOneData);
        dump('----------------saveToDB---------------');
        dump('-----------------------------------');
        dump($hasManyData);
        $user->userHasoneCertificate()->updateOrCreate(
            ['user_id' => $user->id],
            ['data' => $hasManyData]
        );
    }
}