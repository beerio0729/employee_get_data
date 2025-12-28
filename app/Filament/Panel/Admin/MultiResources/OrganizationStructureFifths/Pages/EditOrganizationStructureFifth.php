<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\OrganizationStructureFifthResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructureFifth extends EditRecord
{
    protected static string $resource = OrganizationStructureFifthResource::class;

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
