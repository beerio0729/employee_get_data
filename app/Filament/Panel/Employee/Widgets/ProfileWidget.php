<?php

namespace App\Filament\Panel\Employee\Widgets;

use Filament\Widgets\Widget;

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
            'isPreEmp' => $user->isPreEmployment(),
            'isPostEmp' => $user->isPostEmployment(),
            'image' => $user->userHasmanyDocEmp()->where('file_name', 'image_profile')->first()->path ?? '/user.png',
        ];
    }
}
