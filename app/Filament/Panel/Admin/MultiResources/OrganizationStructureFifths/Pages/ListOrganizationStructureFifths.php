<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Pages;

use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\OrganizationStructureFifthResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationStructureFifths extends ListRecords
{
    protected static string $resource = OrganizationStructureFifthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
