<?php

namespace App\Filament\Resources\Users;

use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'พนักงาน';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    // ป้องกันไม่ให้ผู้ใช้ทั่วไปเข้าถึง Resource
    public static function canViewAny(): bool
    {
        if (Auth::user()->role_id === 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function canCreate(): bool
    {
        if (Auth::user()->role_id === 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function canEdit($record): bool
    {
        if (Auth::user()->role_id === 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function canDelete($record): bool
    {
        if (Auth::user()->role_id === 1) {
            return true;
        } else {
            return false;
        }
    }
}
