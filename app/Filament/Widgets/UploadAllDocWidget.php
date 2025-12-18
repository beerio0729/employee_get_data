<?php

namespace App\Filament\Widgets;

use Detection\MobileDetect;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;


class UploadAllDocWidget extends Widget
{
    protected string $view = 'filament.widgets.upload-all-doc-widget';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    // public static function canView(): bool
    // {   
    //     $detect = new MobileDetect();
    //     return $detect->isMobile();
    // }

}
