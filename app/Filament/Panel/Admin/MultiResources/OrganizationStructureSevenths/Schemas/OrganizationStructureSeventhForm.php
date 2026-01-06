<?php

namespace App\Filament\Panel\Admin\MultiResources\OrganizationStructureSevenths\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use App\Filament\Panel\Admin\Components\MultiResources\Forms\OrganizationStructureFormComponent;

class OrganizationStructureSeventhForm
{
   public static function configure(Schema $schema, $label, $level): Schema
    {
        return $schema
            ->components([
                ...OrganizationStructureFormComponent::formSeventhComponent($label, $level),
                Fieldset::make('seventh_fill')
                    ->contained(false)
                    ->label('กรอกข้อมูล')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema(OrganizationStructureFormComponent::formComponent($label, $level))
            ])->columns(3);
    }
}
