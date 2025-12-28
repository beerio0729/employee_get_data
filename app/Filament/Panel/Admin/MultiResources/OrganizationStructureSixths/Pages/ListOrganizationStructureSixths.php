<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\OrganizationStructureSixthResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationStructureSixths extends ListRecords
{
    protected static string $resource = OrganizationStructureSixthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
