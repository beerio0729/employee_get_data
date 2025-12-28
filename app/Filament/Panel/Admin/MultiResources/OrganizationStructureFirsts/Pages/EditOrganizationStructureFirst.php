<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\OrganizationStructureFirstResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructureFirst extends EditRecord
{
    protected static string $resource = OrganizationStructureFirstResource::class;

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
