<?php

namespace App\Filament\Widgets;

use Detection\MobileDetect;
use Filament\Widgets\Widget;

class AdditionalWidget extends Widget
{
    protected string $view = 'filament.widgets.additional-widget';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
    
    // public static function canView(): bool
    // {
    //     $detect = new MobileDetect();
    //     return $detect->isMobile();
    // }
}
