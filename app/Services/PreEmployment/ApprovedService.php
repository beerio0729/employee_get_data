<?php

namespace App\Services\PreEmployment;

use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use App\Services\LineSendMessageService;
use Filament\Notifications\Notification;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;

class ApprovedService
{
    public function create($record, array $data): void
    {
        $view_notification = 'view_hired_' . now()->timestamp;
        $workStatus = $record->userHasoneWorkStatus;
        $position = $workStatus->workStatusHasonePostEmp->postEmpBelongToOrg->name_th;
        $hired_date = Carbon::parse($data['hired_at'])->locale('th');
        $hired_date_text = $hired_date->translatedFormat('D ที่ j M ') . $hired_date->year + 543;

        $workStatus->update([
            'work_status_def_detail_id' => WorkStatusDefinationDetail::statusId('approved'),
        ]);

        
        $workStatus->workStatusHasonePostEmp()
            ->updateOrCreate(
                ['work_status_id' => $workStatus->id],
                [
                    'employee_code' => $data['employee_code'],
                    'lowest_org_structure_id' => $data['lowest_org_structure_id'],
                    'post_employment_grade_id' => $data['post_employment_grade_id'],
                    'salary' => $data['salary'],
                    'hired_at' => $data['hired_at'],
                    'manager_id' => $data['manager_id'],
                ]
            );

        $history = $record->userHasoneHistory();
        $history->updateOrCreate(
            ['user_id' => $record->id],
            [
                'data' => [
                    ...$history->first()->data ?? [],
                    [   
                        'event' => 'Employment Approved',
                        'value' => 'approved',
                        'description' => "อนุมัติการจ้างงาน เริ่มงานวันที่ \"{$hired_date_text}\"<br>ตำแหน่ง : \"{$position}\"",
                        'date' => carbon::now()->format('y-m-d H:i:s'),
                    ]
                ],
            ]
        );
        Notification::make() //ต้องรัน Queue
            ->title('แจ้งผลการอนุมัติจ้างงาน')
            ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th}"
                . "<br><br>ทางบริษัทฯ ขอแจ้งให้ทราบว่า<br>
                ท่านได้รับอนุมัติให้เข้ากับงานในตำแหน่ง<br>
                <B>\"{$position}\"</B><br>
                "
                . "โดยเริ่มงานในวัน <B>"
                . $hired_date_text
                . "</B><br>"
                . "<br>ขอแสดงความยินดี และยินดีต้อนรับค่ะ")
            ->actions([
                Action::make($view_notification)
                    ->button()
                    ->label('ทำเครื่องหมายว่าอ่านแล้ว')
                    ->markAsRead(),
            ])
            ->sendToDatabase($record, isEventDispatched: true);

        LineSendMessageService::send($record->provider_id, [
            "เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th}"
                ."\n\nทางบริษัทฯ ขอแจ้งให้ทราบว่า\n"
                ."ท่านได้รับอนุมัติให้เข้ากับงานในตำแหน่ง\n"
                ."\"{$position}\""
                ."\n\nโดยเริ่มงานในวัน "
                . $hired_date_text
                . "\n\nขอแสดงความยินดี และยินดีต้อนรับค่ะ",
        ]);
    }
}
