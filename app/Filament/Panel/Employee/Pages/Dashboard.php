<?php

namespace App\Filament\Panel\Employee\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/*******คือว่าเราเอาปุ่มต่างไปไว้ใน widget ซึ่ง widget จะเรียกใช้ Livewire อีกที ซึ่ง Lirewire จะไปเอา Component มาใช้อีกที*******/

class Dashboard extends BaseDashboard
{
    protected static ?string $title = null;

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'lg' => 3
        ];
    }

    public function getHeading(): string
    {
        return '';
    }
    
}
