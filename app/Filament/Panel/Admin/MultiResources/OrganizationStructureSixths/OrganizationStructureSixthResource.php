<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\Organization\OrganizationStructure;
use App\Filament\Panel\Admin\Overrides\BaseOrganizationStructureResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Pages\CreateOrganizationStructureSixth;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Pages\EditOrganizationStructureSixth;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Pages\ListOrganizationStructureSixths;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Schemas\OrganizationStructureSixthForm;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Tables\OrganizationStructureSixthsTable;


class OrganizationStructureSixthResource extends BaseOrganizationStructureResource
{
    protected static ?string $model = OrganizationStructure::class;

    protected static int $level = 6;
    
    public static function getNavigationSort(): ?int
    {
        return static::$level + 1;
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationStructureSixthForm::configure($schema, static::getModelLabel(), static::$level);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructureSixthsTable::configure($table, static::getModelLabel(), static::$level);
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
            'index' => ListOrganizationStructureSixths::route('/'),
            'create' => CreateOrganizationStructureSixth::route('/create'),
            'edit' => EditOrganizationStructureSixth::route('/{record}/edit'),
        ];
    }
}
