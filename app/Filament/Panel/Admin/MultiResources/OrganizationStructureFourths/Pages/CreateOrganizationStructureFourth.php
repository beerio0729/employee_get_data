<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\OrganizationStructureFourthResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationStructureFourth extends CreateRecord
{
    protected static string $resource = OrganizationStructureFourthResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
