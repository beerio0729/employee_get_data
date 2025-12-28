<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\OrganizationStructureThirdResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationStructureThird extends CreateRecord
{
    protected static string $resource = OrganizationStructureThirdResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
