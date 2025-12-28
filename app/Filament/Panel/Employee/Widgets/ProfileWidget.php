<?php

namespace App\Filament\Panel\Employee\Widgets;

use Filament\Widgets\Widget;

class ProfileWidget extends Widget
{
    protected string $view = 'filament.panel.employee.widgets.profile-widget';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    public function getViewData(): array
    {   $user = auth()->user();
        return [
            'name' => $user->userHasoneIdcard->name_th,
            'last_name' => $user->userHasoneIdcard->last_name_th,
            'pre_employment' => $user->userHasonePreEmployment(),
            'post_employment' => $user->userHasManyPostEmployment(),
            'image' => $user->userHasmanyDocEmp()->where('file_name', 'image_profile')->first()->path ?? '/user.png',
        ];
    }
    
}
