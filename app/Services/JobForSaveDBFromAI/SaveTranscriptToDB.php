<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveTranscriptToDB
{
    public function saveToDB(array $hasOneData, array $hasManyData, $user, array $path): void
    {
        dump($hasOneData);
        dump('----------------saveToDB---------------');
        dump('-----------------------------------');
        dump($hasManyData);


        if (!empty($hasOneData)) {
            $hasOneData['file_path'] = $path[0];
            $user->userHasmanyTranscript()->create($hasOneData);
        }


        if (!empty($hasManyData)) {
            foreach ($hasManyData as $index => $item) {
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
            }
        }
    }
}
