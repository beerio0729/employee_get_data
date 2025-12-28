<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\OrganizationStructureThirdResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructureThird extends EditRecord
{
    protected static string $resource = OrganizationStructureThirdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
