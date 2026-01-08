<?php

namespace App\Filament\Panel\Admin\Resources\OpenPositions\Tables;

use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class OpenPositionsTable
{
    public static function configure(Table $table): Table
    {   
        return $table
            ->recordUrl(null) //ป้องกันไม่ให้กดที่ตารางแล้วแก้ไข้
            ->columns([
                TextColumn::make('PositionBelongsToOrgStructure.name_th')
                    ->label('ตำแหน่งที่เปิดรับ'),
                TextColumn::make('PositionBelongsToOrgStructure.name_en')
                    ->label('ตำแหน่งที่เปิดรับ (EN)')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
