<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\Organization\OrganizationStructure;
use App\Filament\Panel\Admin\Overrides\BaseOrganizationStructureResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Pages\CreateOrganizationStructureFifth;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Pages\EditOrganizationStructureFifth;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Pages\ListOrganizationStructureFifths;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Schemas\OrganizationStructureFifthForm;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Tables\OrganizationStructureFifthsTable;

class OrganizationStructureFifthResource extends BaseOrganizationStructureResource
{
    protected static ?string $model = OrganizationStructure::class;

    protected static int $level = 5;

    public static function getNavigationSort(): ?int
    {
        return static::$level + 1;
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationStructureFifthForm::configure($schema, static::getModelLabel(), static::$level);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructureFifthsTable::configure($table, static::getModelLabel(), static::$level);
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
            'index' => ListOrganizationStructureFifths::route('/'),
            'create' => CreateOrganizationStructureFifth::route('/create'),
            'edit' => EditOrganizationStructureFifth::route('/{record}/edit'),
        ];
    }
}
