<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Cache;
use Filament\Actions\DeleteBulkAction;
use App\Models\Organization\OrganizationLevel;
use App\Models\Organization\OrganizationStructure;
use App\Filament\Panel\Admin\Components\MultiResources\Tables\OrganizationStructureTableComponent;

class OrganizationStructureSecondsTable
{
    public static function configure(Table $table, $label, $level): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query->where('organization_level_id', OrganizationStructure::getLevelId($level))
            )
            ->columns(OrganizationStructureTableComponent::tableParentComponent($label, $level))
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
