<?php

namespace App\Filament\Panel\Admin\Resources\Roles;


use BackedEnum;
use UnitEnum;
use App\Models\Role;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Panel\Admin\Resources\Roles\Pages\EditRole;
use App\Filament\Panel\Admin\Resources\Roles\Pages\ListRoles;
use App\Filament\Panel\Admin\Resources\Roles\Pages\CreateRole;
use App\Filament\Panel\Admin\Resources\Roles\Schemas\RoleForm;
use App\Filament\Panel\Admin\Resources\Roles\Tables\RolesTable;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-s-key';
    
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 12;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $modelLabel = 'กำหนดสิทธิ์การเข้าถึง';
    
    protected static ?string $navigationLabel = 'กำหนดสิทธิ์การเข้าถึง';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
    
    // ป้องกันไม่ให้ผู้ใช้ทั่วไปเข้าถึง Resource
    public static function canViewAny(): bool
    {   $user = auth()->user();
        if ($user->role_id === 1) {
            return true;
        } else {
            return false;
        }
    }

    // public static function canCreate(): bool
    // {
    //     $user = auth()->user();
    //     if ($user->role_id === 1) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    // public static function canEdit($record): bool
    // {
    //     $user = auth()->user();
    //     if ($user->role_id === 1) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    // public static function canDelete($record): bool
    // {
    //     $user = auth()->user();
    //     if ($user->role_id === 1) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
}
