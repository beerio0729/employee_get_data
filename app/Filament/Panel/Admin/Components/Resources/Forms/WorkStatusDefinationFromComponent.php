<?php

namespace App\Filament\Panel\Admin\Components\Resources\Forms;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use App\Models\WorkStatusDefination\WorkStatusDefination;

class WorkStatusDefinationFromComponent
{
    public static function formComponent($emp_state): array
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
            Select::make('work_status_def_id')
                ->label('เลือกสถานะบุคคล')
                ->required()
                ->options(
                    fn() => WorkStatusDefination::where('main_work_status', $emp_state)
                        ->pluck('name_th', 'id')
                ),
            ToggleButtons::make('color')
                ->columnSpan(2)
                ->label('เลือกสีสำหรับสถานะ')
                ->inline()
                ->options([
                    'primary' => 'สีพื้นฐาน',
                    'success' => 'สีเขียว',
                    'warning' => 'สีเหลือง',
                    'danger' => 'สีแดง'
                ])
                ->colors([
                    'primary' => 'primary',
                    'success' => 'success',
                    'warning' => 'warning',
                    'danger' => 'danger'
                ]),
            Hidden::make('code')->disabledOn('edit'),
        ];
    }

    public static function formForPreComponent($emp_state): array
    {
        $columns = self::formComponent($emp_state);
        array_splice($columns, 4, 0, [
            Select::make('work_phase')
                ->required()
                ->label('เลือกช่วงเหตุการณ์')
                ->options(config("workstateconfig.pre_employment_phase_state")),
        ]);

        return $columns;
    }
}
