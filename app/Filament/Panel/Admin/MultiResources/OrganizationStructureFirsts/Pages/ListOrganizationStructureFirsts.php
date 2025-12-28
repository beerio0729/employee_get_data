<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\OrganizationStructureFirstResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationStructureFirsts extends ListRecords
{
    protected static string $resource = OrganizationStructureFirstResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
