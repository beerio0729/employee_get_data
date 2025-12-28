<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\OrganizationStructureFourthResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationStructureFourths extends ListRecords
{
    protected static string $resource = OrganizationStructureFourthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
