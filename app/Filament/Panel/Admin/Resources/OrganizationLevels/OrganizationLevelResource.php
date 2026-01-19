<?php

namespace App\Filament\Panel\Admin\Resources\OrganizationLevels;

use BackedEnum;
use UnitEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Models\Organization\OrganizationLevel;
use App\Filament\Panel\Admin\Resources\OrganizationLevels\Pages\EditOrganizationLevel;
use App\Filament\Panel\Admin\Resources\OrganizationLevels\Pages\ListOrganizationLevels;
use App\Filament\Panel\Admin\Resources\OrganizationLevels\Pages\CreateOrganizationLevel;
use App\Filament\Panel\Admin\Resources\OrganizationLevels\Schemas\OrganizationLevelForm;
use App\Filament\Panel\Admin\Resources\OrganizationLevels\Tables\OrganizationLevelsTable;

class OrganizationLevelResource extends Resource
{
    protected static ?string $model = OrganizationLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $recordTitleAttribute = 'name_th';

    protected static ?string $modelLabel = 'โครงสร้างองค์กร';

    protected static ?string $navigationLabel = 'โครงสร้างองค์กร';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return OrganizationLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationLevelsTable::configure($table);
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
            'index' => ListOrganizationLevels::route('/'),
            'create' => CreateOrganizationLevel::route('/create'),
            'edit' => EditOrganizationLevel::route('/{record}/edit'),
        ];
    }
}
