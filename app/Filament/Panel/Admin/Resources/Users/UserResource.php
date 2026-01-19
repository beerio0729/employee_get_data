<?php

namespace App\Filament\Panel\Admin\Resources\Users;

use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Panel\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Panel\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Panel\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Panel\Admin\Resources\Users\Schemas\UserForm;
use App\Filament\Panel\Admin\Resources\Users\Tables\UsersTable;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    protected static ?string $recordTitleAttribute = 'name_th';

    protected static ?string $modelLabel = 'บุคคลากร';

    protected static ?string $navigationLabel = 'บุคคลากร';
    
    protected static ?int $navigationSort = 2;

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
            //'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'userHasoneResume',
                'userHasoneIdcard',
                'userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail',
                'userHasoneWorkStatus.workStatusHasonePreEmp',
                'userHasoneWorkStatus.workStatusHasonePostEmp',
            ]);
    }


    // ป้องกันไม่ให้ผู้ใช้ทั่วไปเข้าถึง Resource
    /* 
    public static function canViewAny(): bool
    {   $user = auth()->user();
        if (in_array($user->role_id, [1, 2])) {
            return true;
        } else {
            return false;
        }
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (in_array($user->role_id, [1, 2])) {
            return true;
        } else {
            return false;
        }
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (in_array($user->role_id, [1, 2])) {
            return true;
        } else {
            return false;
        }
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (in_array($user->role_id, [1, 2])) {
            return true;
        } else {
            return false;
        }
    }
    */
}
