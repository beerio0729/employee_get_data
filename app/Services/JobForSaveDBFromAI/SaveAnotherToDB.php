<?php

namespace App\Services\JobForSaveDBFromAI;

class SaveAnotherToDB
{
    public function saveToDB(array $hasOneData,array $hasManyData, $user, array $path): void
    {
        dump($path);
        dump('----------------saveToDB---------------');
        dump('-----------------------------------');
        dump($hasManyData);


        foreach ($hasManyData as $index => $item) {
            //dump($item['data']);
            $user->userHasmanyAnotherDoc()->create([
                "doc_type" => $item['doc_type'],
                "data" =>  $item['data'],
                "file_path" => $path[$index],
                "date_of_issue" => $item['date_of_issue'],
                "date_of_expiry" => $item['date_of_expiry'],
                
            ]);
        }
    }
}
