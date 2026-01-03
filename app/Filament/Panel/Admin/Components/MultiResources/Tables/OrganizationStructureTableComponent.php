<?php

namespace App\Filament\Panel\Admin\Components\MultiResources\Tables;


use Filament\Tables\Columns\TextColumn;
use App\Models\Organization\OrganizationStructure;

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
    {   $isLowest = $level === OrganizationStructure::getLevelLowest();
        return [
            ...self::tableComponent($label),
            TextColumn::make('parent.name_th')
                ->label(OrganizationStructure::getLevelCollection($level-1)?->name_th ?? '-')
                ->sortable()
                ->searchable(),
            TextColumn::make('max_count')->label('จำนวนสูงสุด')->visible($isLowest),
        ];
    }

    
}
