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
        User::whereHas('userHasoneWorkStatus', function ($q) {
            $q->where('work_status_def_detail_id', 3) // นัดสัมภาษณ์แล้ว
                ->whereHas('workStatusHasonePreEmp', function ($q2) {
                    $q2->whereNotNull('interview_at')
                        ->where('interview_at', '<', now());
                });
        })
            ->each(function ($q) {

                $workStatus = $q->userHasoneWorkStatus;
                $preEmp = $workStatus?->workStatusHasonePreEmp;

                // อัปเดตสถานะ → ไม่มาสัมภาษณ์
                $workStatus->update([
                    'work_status_def_detail_id' => 5,
                ]);

                // เคลียร์วันสัมภาษณ์
                $preEmp?->update([
                    'interview_at' => null,
                ]);

                Notification::make() // ต้องรัน Queue
                    ->title('แจ้งวันนัดสัมภาษณ์')
                    ->body(
                        "เรียน คุณ {$q->userHasoneIdcard->name_th} {$q->userHasoneIdcard->last_name_th}\n\n"
                            . "<br><br>ทางบริษัทฯ ขอแจ้ง<br>❌ ยกเลิกการสัมภาษณ์<br>
                                เนื่องจากท่านไม่มาสัมภาษณ์ตามที่นัดหมายไว้ในวันที่<br><B>"
                            . Carbon::parse($preEmp?->interview_at)->locale('th')->translatedFormat('d M ')
                            . (Carbon::parse($preEmp?->interview_at)->year + 543)
                            . "\nเวลา "
                            . Carbon::parse($preEmp?->interview_at)->format(' H:i')
                            . " น.\n\n</B>"
                    )
                    ->actions([
                        Action::make('view_interview_' . now()->timestamp)
                            ->button()
                            ->label('ทำเครื่องหมายว่าอ่านแล้ว')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($q, isEventDispatched: true);

                LineSendMessageService::send($q->provider_id, [
                    "เรียน คุณ {$q->userHasoneIdcard->name_th} {$q->userHasoneIdcard->last_name_th}\n\n"
                        . "ทางบริษัทฯ ขอแจ้ง ❌ ยกเลิกการสัมภาษณ์\n
                            เนื่องจากท่านไม่มาสัมภาษณ์ตามที่นัดหมายไว้ในวันที่\n\n"
                        . Carbon::parse($preEmp?->interview_at)->locale('th')->translatedFormat('d M ')
                        . (Carbon::parse($preEmp?->interview_at)->year + 543)
                        . "\nเวลา "
                        . Carbon::parse($preEmp?->interview_at)->format(' H:i')
                        . " น.\n\n",
                ]);
            });
    }
}
