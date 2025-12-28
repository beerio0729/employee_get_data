<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\OrganizationStructureSixthResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructureSixth extends EditRecord
{
    protected static string $resource = OrganizationStructureSixthResource::class;

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
