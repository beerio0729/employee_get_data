<?php

namespace App\Filament\Panel\Admin\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Panel\Admin\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
