<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\Organization\OrganizationStructure;
use App\Filament\Panel\Admin\Overrides\BaseOrganizationStructureResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Pages\CreateOrganizationStructureThird;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Pages\EditOrganizationStructureThird;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Pages\ListOrganizationStructureThirds;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Schemas\OrganizationStructureThirdForm;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Tables\OrganizationStructureThirdsTable;


class OrganizationStructureThirdResource extends BaseOrganizationStructureResource
{
    protected static ?string $model = OrganizationStructure::class;
    
    protected static int $level = 3;
    
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return OrganizationStructureThirdForm::configure($schema, static::getModelLabel(), static::$level);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructureThirdsTable::configure($table, static::getModelLabel(), static::$level);
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
            'index' => ListOrganizationStructureThirds::route('/'),
            'create' => CreateOrganizationStructureThird::route('/create'),
            'edit' => EditOrganizationStructureThird::route('/{record}/edit'),
        ];
    }
}
