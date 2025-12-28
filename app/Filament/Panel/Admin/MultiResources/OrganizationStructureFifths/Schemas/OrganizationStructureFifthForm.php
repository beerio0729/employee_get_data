<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureFifthForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components(OrganizationStructureFormComponent::formFifthComponent($label, $level));
    }
}
