<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    User::where('work_status', 'applicant') // เลือกเฉพาะ user ที่ยังเป็นผู้สมัคร
        ->whereHas('userHasoneApplicant', function ($q) {
            //dump(now()->setTimezone('Asia/Bangkok')->format('Y-m-d H:i:s')); // กรองเฉพาะ user ที่มีความสัมพันธ์ applicant และตรงเงื่อนไขข้างใน
            $q->where('status', 'interview_scheduled') // สถานะต้องเป็นนัดสัมภาษณ์แล้ว
                ->whereNotNull('interview_at') // ต้องมีวันที่นัดสัมภาษณ์
                ->where('interview_at', '<', now()->format('Y-m-d H:i:s')); // วันที่นัดสัมภาษณ์ต้องเลยเวลาปัจจุบันแล้ว
        })
        ->each(function ($user) { // วนทีละ user ที่ผ่านเงื่อนไขทั้งหมดด้านบน
            $user->userHasoneApplicant()->update([
                'status' => 'no_interviewed', // อัปเดตสถานะผู้สมัครเป็น ไม่มาสัมภาษณ์
            ]);
        });
})->everySixHours();
