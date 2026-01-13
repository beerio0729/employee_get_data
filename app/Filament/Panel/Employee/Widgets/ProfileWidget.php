<?php

namespace App\Filament\Panel\Employee\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use App\Services\GoogleCalendarService;

class ProfileWidget extends Widget
{
    protected string $view = 'filament.panel.employee.widgets.profile-widget';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = [
        'refreshProfileWidget' => '$refresh',
    ];

    public function getViewData(): array
    {
        $user = auth()->user();
        return [
            'name' => $user->userHasoneIdcard->name_th,
            'last_name' => $user->userHasoneIdcard->last_name_th,
            'work_status' => $user->userHasoneWorkStatus(),
            'meeting_link' => $this->getMeetingLink($user),
            'isPreEmp' => $user->isPreEmployment(),
            'isPostEmp' => $user->isPostEmployment(),
            'image' => $user->userHasmanyDocEmp()->where('file_name', 'image_profile')->first()->path ?? '/user.png',
        ];
    }

    public function getMeetingLink($user)
    {
        $pre_emp = $user?->userHasoneWorkStatus?->workStatusHasonePreEmp;
        $work_phase = $user?->userHasoneWorkStatus?->workStatusBelongToWorkStatusDefDetail?->work_phase;
        $calendar = new GoogleCalendarService();
        $calendar_data = $calendar->getEvent($pre_emp?->google_calendar_id);
        $startTime  = Carbon::parse($pre_emp?->interview_at)->startOfMinute()->subMinutes(30);

        return
            filled($calendar_data?->hangoutLink)
            && $work_phase === "interview_scheduled_time"
            && now()->startOfMinute()->gte($startTime)
            ? $calendar_data->hangoutLink : null;
    }
}
