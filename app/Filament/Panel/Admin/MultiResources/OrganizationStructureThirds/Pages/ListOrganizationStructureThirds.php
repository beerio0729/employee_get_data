<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\OrganizationStructureThirdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationStructureThirds extends ListRecords
{
    protected static string $resource = OrganizationStructureThirdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
