<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DownLoadPDFWidget extends Widget
{
    protected string $view = 'filament.widgets.download-pdf-widget';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
}
