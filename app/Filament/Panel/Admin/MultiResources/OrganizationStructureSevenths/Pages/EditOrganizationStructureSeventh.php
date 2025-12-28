<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\OrganizationStructureSeventhResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructureSeventh extends EditRecord
{
    protected static string $resource = OrganizationStructureSeventhResource::class;

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
