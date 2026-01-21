<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\WorkStatusDefination\WorkStatusDefination;
use App\Filament\Panel\Admin\Components\Resources\Tables\WorkStatusDefinationTableComponent;

class PostEmploymentStatusDefinationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->whereRelation(
                    'workStatusDefDetailBelongsToWorkStatusDef',
                    'main_work_status',
                    'post_employment'
                )
            )
            ->columns([
                ...WorkStatusDefinationTableComponent::tableForPostComponent(),
            ])
            ->filters([
                //
            ])->deferFilters(false)
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
