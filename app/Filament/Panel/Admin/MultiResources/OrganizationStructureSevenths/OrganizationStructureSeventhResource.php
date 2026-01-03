<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\Organization\OrganizationStructure;
use App\Filament\Panel\Admin\Overrides\BaseOrganizationStructureResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Pages\CreateOrganizationStructureSeventh;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Pages\EditOrganizationStructureSeventh;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Pages\ListOrganizationStructureSevenths;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Schemas\OrganizationStructureSeventhForm;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Tables\OrganizationStructureSeventhsTable;

class OrganizationStructureSeventhResource extends BaseOrganizationStructureResource
{
    protected static ?string $model = OrganizationStructure::class;

    protected static int $level = 7;
    
    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return OrganizationStructureSeventhForm::configure($schema, static::getModelLabel(), static::$level);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructureSeventhsTable::configure($table, static::getModelLabel(), static::$level);
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
            'index' => ListOrganizationStructureSevenths::route('/'),
            'create' => CreateOrganizationStructureSeventh::route('/create'),
            'edit' => EditOrganizationStructureSeventh::route('/{record}/edit'),
        ];
    }
}
