<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSixths\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureSixthForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components([
                ...OrganizationStructureFormComponent::formSixthComponent($label, $level),
                Fieldset::make('sixth_fill')
                    ->contained(false)
                    ->label('กรอกข้อมูล')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema(OrganizationStructureFormComponent::formComponent($label, $level))
            ])->columns(5);
    }
}
