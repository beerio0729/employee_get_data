<?php

namespace App\Filament\Panel\Admin\Resources\CompanyProfiles;

use App\Filament\Panel\Admin\Resources\CompanyProfiles\Pages\CreateCompanyProfile;
use App\Filament\Panel\Admin\Resources\CompanyProfiles\Pages\EditCompanyProfile;
use App\Filament\Panel\Admin\Resources\CompanyProfiles\Pages\ListCompanyProfiles;
use App\Filament\Panel\Admin\Resources\CompanyProfiles\Schemas\CompanyProfileForm;
use App\Filament\Panel\Admin\Resources\CompanyProfiles\Tables\CompanyProfilesTable;
use App\Models\CompanyProfile;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyProfileResource extends Resource
{
    protected static ?string $model = CompanyProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;
    
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 13;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $modelLabel = 'ข้อมูลบริษัท';
    
    protected static ?string $navigationLabel = 'ข้อมูลบริษัท';

    public static function form(Schema $schema): Schema
    {
        return CompanyProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyProfilesTable::configure($table);
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
            'index' => ListCompanyProfiles::route('/'),
            'create' => CreateCompanyProfile::route('/create'),
            'edit' => EditCompanyProfile::route('/{record}/edit'),
        ];
    }
}
