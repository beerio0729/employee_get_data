<?php

namespace App\Filament\Panel\Admin\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Panel\Admin\Resources\Roles\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
