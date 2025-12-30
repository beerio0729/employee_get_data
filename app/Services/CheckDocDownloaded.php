<?php

namespace App\Services;

class checkDocDownloaded {
    public static function check($record) :array
    {
        // ดึง prefix จากบัตรประชาชน (ถ้ามี)
        $prefix = $record->userHasoneIdcard?->prefix_name_en;
        // ตรวจว่าเป็นผู้หญิงหรือไม่
        $isFemale = in_array(trim(strtolower($prefix), "."), ['miss', 'mrs']);
        $errorUplaod = [ //สำหรับเอกสารอับโหลด
            'รูปโปรไฟล์' => $record->userHasmanyDocEmp()->where('file_name', 'image_profile')->exists(),
            'resume'        => $record->userHasoneResume()->exists(),
            'บัตรประชาชน'   => $record->userHasoneIdcard()->exists(),
            'วุฒิการศึกษา'   => $record->userHasmanyTranscript()->exists(),
        ];

        $additional = $record->userHasoneAdditionalInfo;

        $errorInput = [ //สำหรับข้อมูลที่ต้องกรอกเอง
            'บิดา' => blank($record->userHasoneFather->name),
            'มารดา' => blank($record->userHasoneMother->name),
            'ผู้ติดต่อยามฉุกเฉิน' => blank($additional->emergency_name),
            'คำถามสุขภาพ' => blank($additional->medical_condition),
            'คำถามเพิ่มเติม' => blank($additional->know_someone),
        ];

        // ใส่ใบเกณฑ์ทหารเฉพาะกรณี "ไม่ใช่ผู้หญิง"
        if (!$isFemale) {
            $errorUplaod['ใบเกณฑ์ทหาร'] = $record->userHasoneMilitary()->exists();
        }

        // หาเฉพาะรายการที่ยังไม่มีไฟล์
        $missingUplaod = array_keys(array_filter($errorUplaod, fn($v) => $v === false));
        $missingInput = array_keys(array_filter($errorInput, fn($v) => $v === true));
        return [
            'upload' => $missingUplaod,
            'input' => $missingInput,
        ];
    }
}