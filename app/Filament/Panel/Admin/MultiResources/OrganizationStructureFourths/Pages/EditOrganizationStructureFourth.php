<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\OrganizationStructureFourthResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructureFourth extends EditRecord
{
    protected static string $resource = OrganizationStructureFourthResource::class;

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
