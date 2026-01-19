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
                Repeater::make('grade_emp')
                    ->columnSpanFull()
                    ->addActionLabel('เพิ่มข้อมูล')
                    ->compact()
                    ->hiddenLabel()
                    ->table([
                        TableColumn::make('ระดับ')->width('40px')->alignment(Alignment::Center)->wrapHeader(),
                        TableColumn::make('ชื่อระดับพนักงาน (TH)')->alignment(Alignment::Center),
                        TableColumn::make('ชื่อระดับพนักงาน (EN)')->alignment(Alignment::Center),
                    ])
                    ->schema([
                        TextInput::make('grade')
                            ->extraAlpineAttributes(['style' => 'text-align: center']),
                        TextInput::make('name_th')->extraAlpineAttributes(['style' => 'text-align: center']),
                        TextInput::make('name_en')->extraAlpineAttributes(['style' => 'text-align: center']),
                    ])
            ]);
    }
}
