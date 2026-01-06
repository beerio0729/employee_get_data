<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureSecondForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                OrganizationStructureFormComponent::formSecondComponent($label, $level),
                ...OrganizationStructureFormComponent::formComponent($label, $level),
            ]);
    }
}
