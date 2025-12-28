<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveMaritalToDB {
    public function saveToDB(array $hasOneData, array $hasManyData, $user): void
    {   
        $hasOneData['status'] = $hasOneData['type'];
        $user->userHasoneMarital()->updateOrCreate(
            ['user_id' => $user->id],
            $hasOneData,
        );
    }
}