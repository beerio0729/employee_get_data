<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions\Action;
use App\Services\GoogleCalendarService;
use App\Services\LineSendMessageService;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;

class noInterviewJob implements ShouldQueue
{
    use Queueable;
    public function handle(): void
    {
        User::whereHas('userHasoneWorkStatus', function ($q) {
            $q->where('work_status_def_detail_id', WorkStatusDefinationDetail::statusId('interview_scheduled')) // นัดสัมภาษณ์แล้ว
                ->whereHas('workStatusHasonePreEmp', function ($q2) {
                    $q2->whereNotNull('start_interview_at')
                        ->where('start_interview_at', '<', now());
                });
        })
            ->each(function ($q) {
                dump($q);
                $workStatus = $q->userHasoneWorkStatus;
                $preEmp = $workStatus?->workStatusHasonePreEmp;
                $dt = Carbon::parse($preEmp?->start_interview_at)->locale('th');
                $calendar_id = $q?->userHasoneWorkStatus?->workStatusHasonePreEmp?->google_calendar_id;
                $calendar = new GoogleCalendarService();
                $calendar->deleteEvent($calendar_id);
                // อัปเดตสถานะ → ไม่มาสัมภาษณ์
                $workStatus->update([
                    'work_status_def_detail_id' => WorkStatusDefinationDetail::statusId('no_interviewed'),
                ]);

                $history = $q->userHasoneHistory();
                $history->updateOrCreate(
                    ['user_id' => $q->id],
                    [
                        'data' => [
                            ...$history->first()->data ?? [],
                            [
                                'event' => 'no interviewed',
                                'description' => "ผิดนัดสัมภาษณ์ เพราะไม่มาสัมภาษณ์ตามนัดใน<br>วัน"
                                    . $dt->translatedFormat('D ที่ j M ')
                                    . ($dt->year + 543)
                                    . " เวลา "
                                    . $dt->format(' H:i')
                                    . " น.",
                                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                            ]
                        ],
                    ]
                );

                // เคลียร์วันสัมภาษณ์
                $preEmp?->update([
                    // 'start_interview_at' => null,
                    // 'end_interview_at' => null,
                    // 'interview_channel' => null,
                    'google_calendar_id' => null
                ]);
                Notification::make() // ต้องรัน Queue
                    ->title('แจ้งวันนัดสัมภาษณ์')
                    ->body(
                        "เรียน คุณ {$q->userHasoneIdcard->name_th} {$q->userHasoneIdcard->last_name_th}\n\n"
                            . "<br><br>ทางบริษัทฯ ขอแจ้ง<br>❌ ยกเลิกการสัมภาษณ์<br>
                                เนื่องจากท่านไม่มาสัมภาษณ์ตามที่นัดหมายไว้ใน<br><B>วัน"
                            . $dt->translatedFormat('D ที่ j M ')
                            . $dt->year + 543
                            . "\nเวลา "
                            . $dt->format(' H:i')
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
                            เนื่องจากท่านไม่มาสัมภาษณ์ตามที่นัดหมายไว้ใน\n\nวัน"
                        . $dt->translatedFormat('D ที่ j M ')
                        . $dt->year + 543
                        . "\nเวลา "
                        . $dt->format(' H:i')
                        . " น.\n\n",
                ]);
            });
    }
    
}
