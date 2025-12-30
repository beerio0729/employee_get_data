<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Panel\Admin\Components\Resources\Tables\WorkStatusDefinationTableComponent;


class PreEmployMentStatusDefinationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->whereRelation(
                    'workStatusDefDetailBelongsToWorkStatusDef',
                    'main_work_status',
                    'pre_employment'
                )
            )
            ->columns([
                ...WorkStatusDefinationTableComponent::tableForPreComponent(),
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
