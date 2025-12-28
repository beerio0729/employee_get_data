<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\OrganizationStructureSecondResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructureSecond extends EditRecord
{
    protected static string $resource = OrganizationStructureSecondResource::class;

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
