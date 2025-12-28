<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFourths\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureFourthForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components(OrganizationStructureFormComponent::formFourthComponent($label, $level));
    }
}
