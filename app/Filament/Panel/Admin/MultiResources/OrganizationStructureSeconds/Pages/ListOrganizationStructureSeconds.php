<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\OrganizationStructureSecondResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationStructureSeconds extends ListRecords
{
    protected static string $resource = OrganizationStructureSecondResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
