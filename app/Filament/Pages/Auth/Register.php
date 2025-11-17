<?php

namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Model;
use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        $parentForm = parent::form($schema);
        $mycomponents = $parentForm->getComponents();
        $hidden = Hidden::make('role_id')->default(4);
        array_splice($mycomponents, 1, 0,[$hidden]);
        return $schema->schema($mycomponents);
    }
    
    protected function handleRegistration(array $data): Model
    {   $user = parent::handleRegistration($data);
        DB::transaction(function () use ($user) {   
        $user->userHasoneResume()->create([]);
        },attempts: 5);
        return $user;
    }
}
