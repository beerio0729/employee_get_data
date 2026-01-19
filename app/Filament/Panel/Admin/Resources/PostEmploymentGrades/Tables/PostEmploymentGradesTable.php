<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class PostEmploymentGradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('grade')
                    ->label('รหัส')->formatStateUsing(fn($state) => "G{$state}")->alignCenter(),
                TextColumn::make('name_th')->label('ชือระดับพนักงาน (TH)')->alignCenter(),
                TextColumn::make('name_en')->label('ชือระดับพนักงาน (EN)')->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
