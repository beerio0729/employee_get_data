<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DownLoadPdfWidget extends Widget
{
    protected string $view = 'filament.widgets.down-load-pdf-widget';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
}
