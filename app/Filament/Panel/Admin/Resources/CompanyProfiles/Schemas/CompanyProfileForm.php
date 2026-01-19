<?php

namespace App\Filament\Panel\Admin\Resources\CompanyProfiles\Schemas;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use App\Models\Geography\Districts;
use App\Models\Geography\Provinces;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Icon;
use App\Models\Geography\Subdistricts;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\FusedGroup;

class CompanyProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('info')
                    ->columnSpanFull()
                    ->columns(2)
                    ->label('ข้อมูลทั่วไป')
                    ->schema([
                        FusedGroup::make([
                            Select::make('prefix_type')
                                ->label('ประเภท')
                                ->live()
                                ->options([
                                    'ห้างหุ้นส่วนจำกัด' => 'ห้างหุ้นส่วนจำกัด',
                                    'บริษัท' => 'บริษัท',
                                ])
                                ->afterStateHydrated(function ($set, $get) {
                                    $name = $get('name');

                                    if (! $name) {
                                        return;
                                    }

                                    $prefix = explode(' ', $name, 2)[0];
                                    $set('prefix_type', $prefix);
                                })
                                ->afterStateUpdated(
                                    fn($state, $set, $get) => $state === "ห้างหุ้นส่วนจำกัด"
                                    ? $set('postfix_type', null)
                                    : $set('name', trim("{$state} {$get('core_name')} {$get('postfix_type')}"))
                                )
                                ->columnSpan(['default' => 1]),
                            TextInput::make('core_name')
                                ->label('ชื่อบริษัท')
                                ->live(onBlur: true)
                                ->placeholder('ชื่อนิติบุคคล')
                                ->dehydrated(false)
                                ->afterStateHydrated(function ($set, $get) {
                                    $name = $get('name');
                                    if (! $name) {
                                        return;
                                    }
                                    $core_name = explode(' ', $name, 3)[1];
                                    $set('core_name', $core_name);
                                })
                                ->afterStateUpdated(
                                    fn($state, $set, $get) =>
                                    $set('name', trim("{$get('prefix_type')} {$state} {$get('postfix_type')}"))
                                )
                                ->columnSpan(fn($get) => $get('prefix_type') === "บริษัท" 
                                    ? ['default' => 1, 'sm' => 2]
                                    : ['default' => 2, 'sm' => 3]),
                            Select::make('postfix_type')
                                ->label('คำลงท้าย')
                                ->visible(fn($get) => $get('prefix_type') === "บริษัท")
                                ->live()
                                ->options([
                                    'จำกัด' => 'จำกัด',
                                    'จำกัด (มหาชน)' => 'จำกัด (มหาชน)',
                                ])
                                ->afterStateHydrated(function ($set, $get) {
                                    $name = $get('name');
                                    if (! $name) {
                                        return;
                                    }
                                    $postfix = explode(' ', $name, 3)[2] ?? null;
                                    $set('postfix_type', $postfix);
                                })
                                ->afterStateUpdated(
                                    fn($state, $set, $get) => 
                                    $set('name', trim("{$get('prefix_type')} {$get('core_name')} {$state}"))
                                )->columnSpan(['default' => 1]),

                        ])->label('ชื่อนิติบุคคล')->columns(['default' => 3, 'md' => 4]),
                        Hidden::make('name'), // ✔ ส่งค่าไป save
                        TextInput::make('tax_id')
                            ->label('เลขประจำตัวผู้เสียภาษี')
                            ->mask('9-9999-99999-99-9')
                            ->dehydrateStateUsing(fn($state) => str_replace('-', '', $state)),
                    ]),
                Fieldset::make('address_info')
                    ->columnSpanFull()
                    ->columns(4)
                    ->label('ที่อยู่')
                    ->schema([
                        Textarea::make('address')
                            ->Label('รายละเอียดที่อยู่')
                            ->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                            ->columnSpanFull()
                            ->autosize()
                            ->trim(),
                        Select::make('province_id')
                            ->options(Provinces::pluck('name_th', 'id'))
                            ->live()
                            ->preload()
                            ->Label('จังหวัด')
                            ->placeholder('จังหวัด')
                            ->searchable()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state == null) {
                                    $set('province_id', null);
                                    $set('district_id', null);
                                    $set('subdistrict_id', null);
                                    $set('zipcode', null);
                                }
                            }),
                        Select::make('district_id')
                            ->options(function ($get) {
                                $data = Districts::where('province_id', $get('province_id'))
                                    ->pluck('name_th', 'id');
                                return $data;
                            })
                            ->live()
                            ->preload()
                            ->Label('อำเภอ')
                            ->placeholder('อำเภอ')
                            ->searchable()
                            ->afterStateUpdated(function ($set) {
                                $set('subdistrict_id', null);
                                $set('zipcode', null);
                            }),
                        Select::make('subdistrict_id')
                            ->options(function ($get) {
                                $data = Subdistricts::where('district_id', $get('district_id'))
                                    ->pluck('name_th', 'id');
                                return $data;
                            })
                            ->Label('ตำบล')
                            ->preload()
                            ->placeholder('ตำบล')
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(function ($state, $set) {
                                $zipcode = Subdistricts::where('id', $state)->pluck('zipcode'); //ไปที่ Subdistrict โดยที่ id = ปัจจุบันที่เราเลือก
                                $set('zipcode', Str::slug($zipcode)); //เอาค่าที่ได้ซึ่งเป็นอาเรย์มาถอดให้เหลือค่าอย่างเดียวด้วย Str::slug()แล้วเอาค่าที่ได้มาใส่ และส่งค่าไปยัง ฟิลด์ที่เลือกในที่นี้คือ zipcode
                            }),
                        TextInput::make('zipcode')
                            ->live()
                            ->Label('รหัสไปรษณีย์')
                            ->placeholder('รหัสไปรษณีย์')
                    ]),
                Fieldset::make('contact_info')
                    ->columnSpanFull()
                    ->columns(2)
                    ->label('ข้อมูลติดต่อ')
                    ->schema([
                        TextInput::make('phone')
                            ->columnSpan(1)
                            ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                            ->mask('999-999-9999')
                            ->label('เบอร์โทรศัพท์')
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                        TextInput::make('email')
                            ->label('อีเมล')
                            ->email(),
                    ])

            ]);
    }
}
