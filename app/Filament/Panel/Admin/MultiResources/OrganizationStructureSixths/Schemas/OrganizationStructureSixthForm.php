<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureSixthForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components(OrganizationStructureFormComponent::formSixthComponent($label, $level));
    }
}
