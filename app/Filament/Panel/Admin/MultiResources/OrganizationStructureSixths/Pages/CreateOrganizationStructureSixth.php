<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\OrganizationStructureSixthResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationStructureSixth extends CreateRecord
{
    protected static string $resource = OrganizationStructureSixthResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
