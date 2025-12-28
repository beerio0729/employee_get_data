<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\OrganizationStructure;
use App\Filament\Panel\Admin\Overrides\BaseOrganizationStructureResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Pages\EditOrganizationStructureFourth;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Pages\ListOrganizationStructureFourths;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Pages\CreateOrganizationStructureFourth;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Schemas\OrganizationStructureFourthForm;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Tables\OrganizationStructureFourthsTable;

class OrganizationStructureFourthResource extends BaseOrganizationStructureResource
{
    protected static ?string $model = OrganizationStructure::class;

    protected static int $level = 4;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return OrganizationStructureFourthForm::configure($schema, static::getModelLabel(), static::$level);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructureFourthsTable::configure($table, static::getModelLabel(), static::$level);
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
            'index' => ListOrganizationStructureFourths::route('/'),
            'create' => CreateOrganizationStructureFourth::route('/create'),
            'edit' => EditOrganizationStructureFourth::route('/{record}/edit'),
        ];
    }
}
