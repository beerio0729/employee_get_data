<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\OrganizationStructureSeventhResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationStructureSevenths extends ListRecords
{
    protected static string $resource = OrganizationStructureSeventhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
