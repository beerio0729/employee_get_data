<?php

namespace App\Filament\Panel\Admin\Resources\OrganizationLevels\Pages;

use App\Filament\Panel\Admin\Resources\OrganizationLevels\OrganizationLevelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationLevel extends CreateRecord
{
    protected static string $resource = OrganizationLevelResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
