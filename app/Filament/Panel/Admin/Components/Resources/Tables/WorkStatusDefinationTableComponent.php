<?php

namespace App\Filament\Panel\Admin\Components\Resources\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class WorkStatusDefinationTableComponent
{
    public static function tableComponent()
    {
        return [
            TextColumn::make('name_th')
                ->label('ชื่อสถานะ')
                ->searchable()
                ->sortable(),
            TextColumn::make('name_en')
                ->formatStateUsing(fn($state) => ucwords($state))
                ->label('Status Name')
                ->searchable()
                ->sortable(),
            ToggleColumn::make('is_active')
                ->label('Active')
                ->grow(false)
                ->searchable()
                ->sortable(),
        ];
    }

    public static function tableForPreComponent(): array
    {
        $columns = self::tableComponent();

        array_splice($columns, 2, 0, [
            TextColumn::make('work_phase')
                ->formatStateUsing(
                    fn($state) =>
                    config("workstateconfig.pre_employment_phase_state.{$state}")
                )
                ->label('ช่วงเหตุการณ์')
                ->searchable()
                ->sortable(),
        ]);

        return $columns;
    }


    public static function tableForPostComponent(): array
    {
        $columns = self::tableComponent();

        array_splice($columns, 2, 0, [
            TextColumn::make(
                'workStatusDefinationDetailBelongsToWorkStatusDefination.name_th'
            )
                ->label('สถานะบุคคล')
                ->searchable()
                ->sortable(),
        ]);

        return $columns;
    }
}
