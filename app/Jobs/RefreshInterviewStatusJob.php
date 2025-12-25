<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions\Action;
use App\Services\LineSendMessageService;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefreshInterviewStatusJob implements ShouldQueue
{
    use Queueable;
    public function handle(): void
    {
        User::where('work_status', 'applicant') // เลือกเฉพาะ user ที่ยังเป็นผู้สมัคร
            ->whereHas('userHasoneApplicant', function ($q) {
                $q->where('status', 'interview_scheduled') // สถานะต้องเป็นนัดสัมภาษณ์แล้ว
                    ->whereNotNull('interview_at') // ต้องมีวันที่นัดสัมภาษณ์
                    ->where('interview_at', '<', now()->format('Y-m-d H:i:s')); // วันที่นัดสัมภาษณ์ต้องเลยเวลาปัจจุบันแล้ว
            })
            ->each(function ($q) { // วนทีละ user ที่ผ่านเงื่อนไขทั้งหมดด้านบน
                $q->userHasoneApplicant()->update([
                    'status' => 'no_interviewed', // อัปเดตสถานะผู้สมัครเป็น ไม่มาสัมภาษณ์
                ]);
                Notification::make() //ต้องรัน Queue
                    ->title('แจ้งวันนัดสัมภาษณ์')
                    ->body("เรียน คุณ {$q->userHasoneIdcard->name_th} {$q->userHasoneIdcard->last_name_th} \n\n"
                        . "<br><br>ทางบริษัทฯ ขอแจ้ง<br>❌ ยกเลิกการสัมภาษณ์<br>
                                    เนื่องจากท่านไม่มาสัมภาษณ์ตามที่นัดหมายไว้ในวันที่<br>
                                            <B>"
                        . Carbon::parse($q->userHasoneApplicant->interview_at)->locale('th')->translatedFormat('d M ')
                        . (Carbon::parse($q->userHasoneApplicant->interview_at)->year + 543)
                        . "\nเวลา "
                        . Carbon::parse($q->userHasoneApplicant->interview_at)->format(' H:i')
                        . " น.\n\n"
                        . "</B>")
                    ->actions([
                        Action::make('view_interview_' . now()->timestamp)
                            ->button()
                            ->label('ทำเครื่องหมายว่าอ่านแล้ว')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($q, isEventDispatched: true);
                LineSendMessageService::send($q->provider_id, [
                    "เรียน คุณ {$q->userHasoneIdcard->name_th} {$q->userHasoneIdcard->last_name_th} \n\n"
                        . "ทางบริษัทฯ ขอแจ้ง ❌ ยกเลิกการสัมภาษณ์
                                    \nเนื่องจากท่านไม่มาสัมภาษณ์ตามที่นัดหมายไว้ในวันที่\n\n"
                        . Carbon::parse($q->userHasoneApplicant->interview_at)->locale('th')->translatedFormat('d M ')
                        . (Carbon::parse($q->userHasoneApplicant->interview_at)->year + 543)
                        . "\nเวลา "
                        . Carbon::parse($q->userHasoneApplicant->interview_at)->format(' H:i')
                        . " น.\n\n",
                ]);
            });
    }
}
