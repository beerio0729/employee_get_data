<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\OrganizationStructureFifthResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationStructureFifth extends CreateRecord
{
    protected static string $resource = OrganizationStructureFifthResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
