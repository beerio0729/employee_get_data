<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveMilitaryToDB {
    public function saveToDB(array $hasOneData, array $hasManyData, $user): void
    {   dump($hasOneData);
        $user->userHasoneMilitary()->updateOrCreate(
            ['user_id' => $user->id],
            $hasOneData
        );
    }
}