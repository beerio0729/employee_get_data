<?php

namespace App\Services\JobForSaveDBFromAI;

use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Subdistricts;


class SaveIdcardToDB
{
    public function saveToDB(array $hasOneData, array $hasManyData, $user): void
    {
        $hasOneDataSuccess = $this->saveHasOneResumeLocation($hasOneData);
        //dump($hasOneDataSuccess);
        $user->userHasoneIdcard()->updateOrCreate(
            ['user_id' => $user->id],
            $hasOneDataSuccess
        );
    }


    public function saveHasOneResumeLocation(array $hasOneData): array
    {
        $data = $hasOneData;

        $data['province_id'] = Provinces::where('name_th', $hasOneData['province'])
            ->orWhere('name_en', $hasOneData['province'])
            ->value('id') ?? null;
        $data['district_id'] = Districts::where('name_th', $hasOneData['district'])
            ->where('province_id', $data['province_id'])
            ->orWhere('name_en', $hasOneData['district'])
            ->value('id') ?? null;
        $data['subdistrict_id'] = Subdistricts::where('name_th', $hasOneData['subdistrict'])
            ->where('district_id', $data['district_id'])
            ->orWhere('name_en', $hasOneData['subdistrict'])
            ->value('id') ?? null;
        $data['zipcode'] = Subdistricts::where('name_th', $hasOneData['subdistrict'])
            ->where('district_id', $data['district_id'])
            ->orWhere('name_en', $hasOneData['subdistrict'])
            ->value('zipcode') ?? null;

        // ลบ key เดิมที่ไม่จำเป็น
        unset($data['province'], $data['district'], $data['subdistrict']);

        return $data;
    }
}
