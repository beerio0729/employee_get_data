<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\OrganizationStructureSeventhResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationStructureSeventh extends CreateRecord
{
    protected static string $resource = OrganizationStructureSeventhResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
