<?php

namespace App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class WorkStatusDefinationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_th')
                    ->label('ชื่อสถานะ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_en')
                    ->label('Status Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('main_work_status')
                    ->label('กลุ่มสถานะ')
                    ->formatStateUsing(fn($state) => config("workstateconfig.main_work_status.{$state}"))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('ใช้งาน')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
