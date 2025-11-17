<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('role')
                    ->schema([
                        TextInput::make('name')
                            ->hiddenLabel()
                            ->prefix('สถานะพนักงาน'),
                        Toggle::make('active')
                            ->label('ใช้งาน')
                            ->default(true)
                    ])->columnSpanFull()
            ]);
    }
}
