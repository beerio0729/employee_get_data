<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureSeventhForm
{
   public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components(OrganizationStructureFormComponent::formSeventhComponent($label, $level));
    }
}
