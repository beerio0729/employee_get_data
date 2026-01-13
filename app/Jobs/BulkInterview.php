<?php

namespace App\Jobs;

use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use App\Services\GoogleCalendarService;
use App\Services\LineSendMessageService;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class BulkInterview implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $records;
    protected $action;

    public function __construct(Collection $records, array $data = [], string $action = '')
    {
        $this->data = $data;
        $this->records = $records;
        $this->action = $action;
    }

    public function handle(): void
    {
        if (blank($this->action)) {
            $this->fail();
        }

        if ($this->action === "create") {
            $this->createInterview();
        }

        if ($this->action === "delete") {
            $this->deleteInterview();
        }
    }

    public function createInterview()
    {
        foreach ($this->records as $index => $record) {
            $data_interview = $this->data['multiform_interview'][$index];

            $view_notification = 'view_interview_' . Date::now()->timestamp;
            $workStatus = $record->userHasoneWorkStatus()->first();
            $interview_date = Carbon::parse($data_interview['interview_at'])->locale('th');
            if ($data_interview['interview_channel'] === 'online') {
                $calendar = new GoogleCalendarService();
                $calendar_response = $calendar->createEvent([
                    'start_time' => $data_interview['interview_at'],
                    'duration' => $data_interview['interview_duration'], //ระยะเวลาการประชุม
                    'email' => $record->email,
                    'title' => "นัดประชุมคุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th}",
                ]);
            }

            $workStatus->update([
                'work_status_def_detail_id' => 3,
            ]);

            $workStatus->workStatusHasonePreEmp()->update([
                'google_calendar_id' => $calendar_response?->id ?? null,
                'interview_channel' => $data_interview['interview_channel'],
                'interview_at' => $data_interview['interview_at'],
            ]);
            $history = $record->userHasoneHistory();
            $history->updateOrCreate(
                ['user_id' => $record->id],
                [
                    'data' => [
                        ...$history->first()->data ?? [],
                        [
                            'event' => 'interview scheduled',
                            'description' => "นัดหมายวันนัดสัมภาษณ์ผ่านช่องทาง \"{$data_interview['interview_channel']}\"",
                            'value' => $data_interview['interview_at'],
                            'date' => carbon::now()->format('y-m-d H:i:s'),
                        ]
                    ],
                ]
            );
            Notification::make() //ต้องรัน Queue
                ->title('แจ้งวันนัดสัมภาษณ์')
                ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                    . "<br><br>ทางบริษัทฯ ขอแจ้งนัดหมายวันสัมภาษณ์งานของท่านใน<br><B>วัน"
                    . $interview_date->translatedFormat('D ที่ j M ')
                    . $interview_date->year + 543
                    . "\nเวลา "
                    . $interview_date->format(' H:i')
                    . " น."
                    . "</B>"
                    . "<br>ผ่านช่องทาง <B>\"" . ucwords($data_interview['interview_channel']) . " \"</B>"
                    . "<br><br>โปรดเตรียมเอกสารที่เกี่ยวข้องและมาถึงก่อนเวลานัดหมาย 10 นาที"
                    . "<br>ขอบคุณค่ะ")
                ->actions([
                    Action::make($view_notification)
                        ->button()
                        ->label('ทำเครื่องหมายว่าอ่านแล้ว')
                        ->markAsRead(),
                ])
                ->sendToDatabase($record, isEventDispatched: true);

            LineSendMessageService::send($record->provider_id, [
                "เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                    . "ทางบริษัทฯ ขอแจ้งนัดหมายวันสัมภาษณ์งานของท่านใน\n\nวัน "
                    . $interview_date->translatedFormat('D ที่ j M ')
                    . $interview_date->year + 543
                    . "\nเวลา "
                    . $interview_date->format(' H:i')
                    . " น.\n"
                    . "ผ่านช่องทาง \"" . ucwords($data_interview['interview_channel']) . " \"\n\n"
                    . "โปรดเตรียมเอกสารที่เกี่ยวข้องและมาถึงก่อนเวลานัดหมาย 10 นาที \n\n"
                    . "ขอบคุณค่ะ",
            ]);
        }
    }

    public function deleteInterview()
    {
        foreach ($this->records as $record) {
            $view_notification = 'view_interview_' . Date::now()->timestamp;
            $workStatus = $record->userHasoneWorkStatus()->first();
            $interview_date = $workStatus?->workStatusHasonePreEmp?->interview_at;
            $interview_date = Carbon::parse($interview_date)->locale('th');
            $calendar_id = $record?->userHasoneWorkStatus?->workStatusHasonePreEmp?->google_calendar_id;
            $calendar = new GoogleCalendarService();
            $calendar->deleteEvent($calendar_id);
            $workStatus->update([
                'work_status_def_detail_id' => 2,
            ]);
            $workStatus->workStatusHasonePreEmp()->update([
                'interview_channel' => null,
                'interview_at' => null,
                'google_calendar_id' => null
            ]);

            $history = $record->userHasoneHistory();
            $history->updateOrCreate(
                ['user_id' => $record->id],
                [
                    'data' => [
                        ...$history->first()->data ?? [],
                        [
                            'event' => 'cancel interview',
                            'description' => "ยกเลิกการนัดสัมภาษณ์ของ<br>วัน"
                                . $interview_date->translatedFormat('D ที่ j M ')
                                . ($interview_date->year + 543)
                                . " เวลา "
                                . $interview_date->format(' H:i')
                                . " น.",
                            'date' => carbon::now()->format('y-m-d H:i:s'),
                        ]
                    ],
                ]
            );
            Notification::make() //ต้องรัน Queue
                ->title('แจ้งวันนัดสัมภาษณ์')
                ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                    . "<br><br>ทางบริษัทฯ ขอแจ้ง<br>❌ ยกเลิกนัดหมายวันสัมภาษณ์งานของท่านใน<br>
                                            <B>วัน"
                    . $interview_date->translatedFormat('D ที่ j M ')
                    . ($interview_date->year + 543)
                    . " เวลา "
                    . $interview_date->format(' H:i')
                    . " น."
                    . "</B>"
                    . "<br><br>ขออภัยมา ณ ที่นี้")
                ->actions([
                    Action::make($view_notification)
                        ->button()
                        ->label('ทำเครื่องหมายว่าอ่านแล้ว')
                        ->markAsRead(),
                ])
                ->sendToDatabase($record, isEventDispatched: true);
            LineSendMessageService::send($record->provider_id, [
                "เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                    . "ทางบริษัทฯ ขอแจ้ง 
                                            \n❌ ยกเลิกนัดหมายวันสัมภาษณ์งานของท่านใน\n\nวัน "
                    . $interview_date->translatedFormat('D ที่ j M ')
                    . ($interview_date->year + 543)
                    . "\nเวลา "
                    . $interview_date->format(' H:i')
                    . " น.\n\n"
                    . "ขออภัยมา ณ ที่นี้",
            ]);
        }
    }
}
