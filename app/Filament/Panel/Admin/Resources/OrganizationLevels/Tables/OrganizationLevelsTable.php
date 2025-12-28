<?php

namespace App\Filament\Panel\Admin\Resources\OrganizationLevels\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class OrganizationLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->reorderable('level')
            ->columns([
                TextColumn::make('level')
                    ->prefix('Level ')
                    ->label('ระดับ')
                    ->sortable(),
                TextColumn::make('name_th')
                    ->label('ชื่อระดับองค์กร')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name_en')
                    ->label('ชื่อระดับองค์กร (EN)')
                    ->sortable()
                    ->searchable(),
            ])
            ->reorderRecordsTriggerAction(
                fn(Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'บันทึก' : 'แก้ไข Level'),
            )
            ->defaultSort('level', direction: 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
