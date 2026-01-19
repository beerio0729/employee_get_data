<?php

namespace App\Filament\Panel\Admin\Resources\CompanyProfiles\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class CompanyProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('name'),
                        TextColumn::make('tax_id')
                            ->formatStateUsing(function ($state) {
                                if (! $state) {
                                    return null;
                                }

                                return 'เลขประจำตัวผู้เสียภาษี : ' .
                                    preg_replace(
                                        '/(\d)(\d{4})(\d{5})(\d{2})(\d)/',
                                        '$1-$2-$3-$4-$5',
                                        $state
                                    );
                            }),
                        TextColumn::make('email')
                            ->formatStateUsing(fn($state) => "อีเมล : {$state}"),
                        TextColumn::make('phone')
                            ->formatStateUsing(fn($state) => "เบอร์โทรศัพท์ : {$state}"),
                    ]),
                    Stack::make([
                        TextColumn::make('address')
                            ->formatStateUsing(fn($state) => "ที่อยู่ : {$state}"),
                        TextColumn::make('companyBelongtoSubdistrict.name_th')
                            ->formatStateUsing(fn($state) => "แขวง/ตำบล : {$state}"),
                        TextColumn::make('companyBelongtoDistrict.name_th')
                            ->formatStateUsing(fn($state) => "เขต/อำเภอ : {$state}"),
                        TextColumn::make('companyBelongtoProvince.name_th')
                            ->formatStateUsing(fn($state) => "จังหวัด : {$state}"),
                        TextColumn::make('companyBelongtoSubdistrict.zipcode')
                            ->formatStateUsing(fn($state) => "รหัสไปรษณีย์ : {$state}"),
                    ])
                ])
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
        // ->toolbarActions([
        //     BulkActionGroup::make([
        //         DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
