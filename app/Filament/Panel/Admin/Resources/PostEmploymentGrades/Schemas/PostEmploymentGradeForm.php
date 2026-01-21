<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater\TableColumn;

class PostEmploymentGradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('grade')
                    ->label('ระดับ'),
                TextInput::make('name_th')
                    ->label('ชื่อระดับพนักงาน (TH)'),
                TextInput::make('name_en')
                    ->label('ชื่อระดับพนักงาน (EN)'),

            ])->columns(3);
    }
}
