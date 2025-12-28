<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\OrganizationStructureSecondResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationStructureSecond extends CreateRecord
{
    protected static string $resource = OrganizationStructureSecondResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
