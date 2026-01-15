<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\Organization\OrganizationStructure;
use App\Filament\Panel\Admin\Overrides\BaseOrganizationStructureResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Pages\EditOrganizationStructureSecond;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Pages\ListOrganizationStructureSeconds;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Pages\CreateOrganizationStructureSecond;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Schemas\OrganizationStructureSecondForm;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Tables\OrganizationStructureSecondsTable;

class OrganizationStructureSecondResource extends BaseOrganizationStructureResource
{
    protected static ?string $model = OrganizationStructure::class;

    protected static int $level = 2;

    public static function getNavigationSort(): ?int
    {
        return static::$level + 1;
    }

    public static function form(Schema $schema): Schema
    {   
        return OrganizationStructureSecondForm::configure($schema, static::getModelLabel(), static::$level);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructureSecondsTable::configure($table, static::getModelLabel(), static::$level);
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
            'index' => ListOrganizationStructureSeconds::route('/'),
            'create' => CreateOrganizationStructureSecond::route('/create'),
            'edit' => EditOrganizationStructureSecond::route('/{record}/edit'),
        ];
    }
}
