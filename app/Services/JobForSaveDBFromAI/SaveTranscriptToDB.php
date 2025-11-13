<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveTranscriptToDB {
    public function saveToDB(array $hasOneData, array $hasManyData, $user): void
    {
        $user->userHasoneTranscript()->updateOrCreate(
            ['user_id' => $user->id],
            $hasOneData
        );
    }
}