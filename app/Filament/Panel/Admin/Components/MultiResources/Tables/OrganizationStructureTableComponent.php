<?php

namespace App\Filament\Panel\Admin\Components\MultiResources\Tables;

use App\Models\OrganizationLevel;
use Filament\Tables\Columns\TextColumn;

class OrganizationStructureTableComponent
{
    public static function tableComponent($label): array
    {
        return
            [
                TextColumn::make('name_th')
                    ->label("ชื่อ{$label}")
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name_en')
                    ->label("ชื่อ{$label} (En)")
                    ->sortable()
                    ->searchable(),
                TextColumn::make('code')
                    ->label("Code")
                    ->sortable()
                    ->searchable(),
            ];
    }

    public static function tableParentComponent($label, $level): array
    {
        return [
            ...self::tableComponent($label),
            TextColumn::make('parent.name_th')
                ->label(OrganizationLevel::where('level', $level - 1)->value('name_th'))
                ->sortable()
                ->searchable(),
        ];
    }

    
}
