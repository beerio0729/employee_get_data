<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureFifths\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureFifthForm
{
    public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components([
                ...OrganizationStructureFormComponent::formFifthComponent($label, $level),
                Fieldset::make('fifth_fill')
                    ->contained(false)
                    ->label('กรอกข้อมูล')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema(OrganizationStructureFormComponent::formComponent($label, $level))
            ])->columns(4);
    }
}
