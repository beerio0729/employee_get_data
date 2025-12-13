<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class LoadDocWidget extends Widget
{
    protected string $view = 'filament.widgets.load-doc-widget';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
}
