<?php

namespace App\Services\JobForSaveDBFromAI;

use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Subdistricts;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class SaveResumeToDB
{

    public function saveToDB(array $hasOneData, array $hasManyData, $user): void
    {
        $hasOneDataSuccess = $this->saveHasOneResumeLocation($hasOneData);
        // //dump($user->userHasoneResume->id);
        // // dump('--------------------'); 
        // dump('----------hasOneDataSuccess----------');
        // dump($hasOneDataSuccess);
        // dump('--------------------'); 
        // dump('---------hasManyData-----------');
        dump($hasManyData['position']);
        $resume = $user->userHasoneResume()->updateOrCreate(
            ['user_id' => $user->id],
            $hasOneDataSuccess
        );
        $resume->resumeHasonelocation()->updateOrCreate(
            ['resume_id' => $user->userHasoneResume->id],
            $hasOneDataSuccess
        );
        $resume->resumeHasoneJobPreference()->create(
            [
                ...$hasOneDataSuccess,
                'position' => $hasManyData['position'],
                'location' => $this->getProvinceIds($hasManyData['location'] ?? []),
            ]
        );

        $workExperiences = $hasManyData['work_experience'] ?? [];
        $educations = $hasManyData['education'] ?? [];
        $languageSkills = $hasManyData['lang_skill'] ?? [];
        $skills = $hasManyData['skills'] ?? [];
        $certificates = $hasManyData['certificates'] ?? [];
        $otherContacts = $hasManyData['other_contacts'] ?? [];

        if (!empty($workExperiences)) {
            foreach ($workExperiences as $item) {
                $resume->resumeHasmanyWorkExperiences()->create([
                    "company" => $item['company'],
                    "details" =>  $item['details'],
                    "start" => $item['start'],
                    "last" => $item['last'],
                    "position" => $item['position'],
                    "reason_for_leaving" => $item['reason_for_leaving'],
                    "salary" => $item['salary'],
                ]);
            }
        }

        if (!empty($educations)) {
            foreach ($educations as $item) {
                $resume->resumeHasmanyEducation()->create([
                    'institution' => $item['institution'],       // สถาบัน
                    'degree' => $item['degree'],                 // ชื่อวุฒิการศึกษา
                    'education_level' => $item['education_level'], // ระดับการศึกษา
                    'faculty' => $item['faculty'],               // คณะ
                    'major' => $item['major'],                   // สาขาวิชา
                    'last_year' => $item['last_year'],            // ปีที่สำเร็จการศึกษา
                    'gpa' => (float)$item['gpa'],                // เกรดเฉลี่ย
                ]);
            }
        }

        if (!empty($languageSkills)) {
            foreach ($languageSkills as $item) {
                $resume->resumeHasmanyLangSkill()->create([
                    'language' => $item['language'],
                    'speaking' => $item['speaking'],
                    'listening' => $item['listening'],
                    'writing' => $item['writing'],
                ]);
            }
        }

        if (!empty($skills)) {
            foreach ($skills as $item) {
                $resume->resumeHasmanySkill()->create([
                    'skill_name' => $item['skill_name'],
                    'level' => $item['level'],
                ]);
            }
        }

        if (!empty($certificates)) {
            foreach ($certificates as $item) {
                $resume->resumeHasmanyCertificate()->create([
                    'name' => $item['name'],
                    'date_obtained' => $item['date_obtained'], //ช่วงเวลาที่ได้รับการรับรอง
                ]);
            }
        }

        if (!empty($otherContacts)) {
            foreach ($otherContacts as $item) {
                $resume->resumeHasmanyOtherContact()->create([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'tel' => $item['tel'],
                ]);
            }
        }
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

    function getProvinceIds(array $locations): array
    {
        $ids = [];

        foreach ($locations as $loc) {
            $ids[] = Provinces::where('name_th', $loc)
                ->orWhere('name_en', $loc)
                ->value('id');
        }

        return $ids;
    }
}
