<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdditionalWidget extends Widget
{
    protected string $view = 'filament.widgets.additional-widget';
    protected static ?int $sort = 2;
     // public static function canView(): bool
    // {   
    //     $detect = new MobileDetect();
    //     return $detect->isMobile();
    // }
}
