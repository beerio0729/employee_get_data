<?php

namespace App\Filament\Panel\Admin\Components\Resources\Forms;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

class WorkStatusDefinationFromComponent
{
    public static function formComponent(): array
    {
        return [
            Toggle::make('is_active')
                ->label('ใช้งาน')
                ->columnSpanFull()
                ->default(1),
            TextInput::make('name_th')
                ->required()
                ->label('ชื่อสถานะ'),
            TextInput::make('name_en')
                ->required()
                ->label('Status Name')
                ->live(onBlur: true)
                ->formatStateUsing(fn($state) => ucwords($state))
                ->afterStateUpdated(function ($state, $set) {
                    $set('code', str_replace(' ', '_', strtolower($state)));
                }),
            Hidden::make('code')->disabledOn('edit'),
        ];
    }
}
