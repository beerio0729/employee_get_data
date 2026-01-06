<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureThirds\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureThirdForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components([
                ...OrganizationStructureFormComponent::formThirdComponent($label, $level),
                Fieldset::make('third_fill')
                    ->contained(false)
                    ->label('กรอกข้อมูล')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema(OrganizationStructureFormComponent::formComponent($label, $level)),
            ]);
    }
}
