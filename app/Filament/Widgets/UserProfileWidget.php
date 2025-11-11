<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;


class UserProfileWidget extends Widget
{
    protected string $view = 'filament.widgets.user-profile-widget';
    protected int|string|array $columnSpan = 'full';

    // public static function canView(): bool
    // {
    //     if (Auth::user()->role_id === 1) {
    //         return false;
    //     } else {
    //         return true;
    //     }
    // }
}
