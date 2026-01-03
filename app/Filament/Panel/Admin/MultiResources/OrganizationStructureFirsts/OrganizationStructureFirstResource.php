<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\Organization\OrganizationStructure;
use App\Filament\Panel\Admin\Overrides\BaseOrganizationStructureResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Pages\EditOrganizationStructureFirst;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Pages\ListOrganizationStructureFirsts;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Pages\CreateOrganizationStructureFirst;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Schemas\OrganizationStructureFirstForm;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Tables\OrganizationStructureFirstsTable;

class OrganizationStructureFirstResource extends BaseOrganizationStructureResource
{
    protected static ?string $model = OrganizationStructure::class;

    protected static int $level = 1;
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return OrganizationStructureFirstForm::configure($schema, static::getModelLabel(), static::$level);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructureFirstsTable::configure($table, static::getModelLabel(), static::$level);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationStructureFirsts::route('/'),
            'create' => CreateOrganizationStructureFirst::route('/create'),
            'edit' => EditOrganizationStructureFirst::route('/{record}/edit'),
        ];
    }
}
