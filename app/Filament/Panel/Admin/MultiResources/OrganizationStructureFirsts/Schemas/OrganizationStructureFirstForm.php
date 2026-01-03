<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\Schemas;

use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;
use Filament\Schemas\Schema;

class OrganizationStructureFirstForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->columns(3)
            ->components(OrganizationStructureFormComponent::formFirstComponent($label, $level));
    }
}
