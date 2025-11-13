<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveBookbankToDB {
    public function saveToDB(array $hasOneData, array $hasManyData, $user): void
    {   dump($hasOneData);
        $user->userHasoneBookbank()->updateOrCreate(
            ['user_id' => $user->id],
            $hasOneData
        );
    }
}