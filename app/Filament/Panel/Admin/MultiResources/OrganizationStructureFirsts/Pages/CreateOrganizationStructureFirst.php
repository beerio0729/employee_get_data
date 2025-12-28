<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\OrganizationStructureFirstResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationStructureFirst extends CreateRecord
{
    protected static string $resource = OrganizationStructureFirstResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
