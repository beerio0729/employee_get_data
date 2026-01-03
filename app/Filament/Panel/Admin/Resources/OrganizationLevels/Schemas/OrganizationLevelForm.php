<?php

namespace App\Filament\Panel\Admin\Resources\OrganizationLevels\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class OrganizationLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_th')->label('ชื่อระดับองค์กร'),
                TextInput::make('name_en')->label('ชื่อระดับองค์กร (En)'),
                TextInput::make('level')
                    ->readOnly()
                    ->label('Level')
                    ->default(fn ($model) => $model::count() + 1)
            ])->columns(3);
    }
}
