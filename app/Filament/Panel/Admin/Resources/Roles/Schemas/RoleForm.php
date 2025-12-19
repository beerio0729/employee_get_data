<?php

namespace App\Filament\Panel\Admin\Resources\Roles\Schemas;

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
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->validationMessages([
                                'required' => 'กรุณากรอกสถานะพนักงานภาษาอังกฤษ',
                            ])
                            ->required()
                            ->label('สถานะพนักงานภาษาอังกฤษ')
                            ->prefix('สถานะพนักงานภาษาอังกฤษ'),
                        TextInput::make('name_th')
                            ->validationMessages([
                                'required' => 'กรุณากรอกสถานะพนักงานภาษาไทย',
                            ])
                            ->required()
                            ->label('สถานะพนักงานภาษาไทย')
                            ->prefix('สถานะพนักงานภาษไทย'),
                        Toggle::make('active')
                            ->label('ใช้งาน')
                            ->default(true)
                    ])->columnSpanFull()
            ]);
    }
}
