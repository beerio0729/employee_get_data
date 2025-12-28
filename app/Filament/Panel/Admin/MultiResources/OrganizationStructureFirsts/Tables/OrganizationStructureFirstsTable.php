<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Panel\Admin\Components\MultiResources\Tables\OrganizationStructureTableComponent;
use Filament\Actions\DeleteAction;

class OrganizationStructureFirstsTable
{
    public static function configure(Table $table, $label, $level): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query->where('level', $level)
            )
            ->columns(OrganizationStructureTableComponent::tableComponent($label))
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
