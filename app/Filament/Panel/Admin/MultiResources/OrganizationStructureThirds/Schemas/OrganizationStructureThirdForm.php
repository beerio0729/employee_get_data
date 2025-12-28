<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureThirdForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components(OrganizationStructureFormComponent::formThirdComponent($label, $level));
    }
}
