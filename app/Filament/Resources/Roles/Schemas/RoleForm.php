<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('รายละเอียดกลุ่มสิทธิ์')
                    ->description('หน้านี้ใช้สำหรับกำหนดสิทธิ์การเข้าถึง,สิทธิ์การแก้ไขข้อมูล')
                    ->schema([
                        TextInput::make('name')->hiddenLabel()->prefix('ชื่อกลุ่มสิทธิ์')->required(),
                        Toggle::make('active')->default(1),
                        Repeater::make('rolepermissions')
                            ->label('กำหนดสิทธิ์')
                            ->relationship('rolepermissions')
                            ->schema([
                                Toggle::make('active')->label('เปิดใช้งาน'),
                                Select::make('permission_id')
                                    ->label(false)
                                    ->options(Permission::where('active', 1)->pluck('model_name', 'id'))
                                    ->required()
                                    ->prefix('ส่วนที่ให้เข้าถึง'),
                                Toggle::make('is_viewAny')->label('ดูตาราง')->onColor('danger'),
                                Toggle::make('is_view')->label('ดูข้อมูล')->onColor('info'),
                                Toggle::make('is_create')->label('สร้าง')->onColor('danger'),
                                Toggle::make('is_update')->label('แก้ไข้')->onColor('danger'),
                                Toggle::make('is_delete')->label('ลบชั่วคราว')->onColor('danger'),
                                Toggle::make('is_forceDelete')->label('ลบถาวร')->onColor('danger'),
                                Toggle::make('is_restore')->label('กู้ข้อมูล')->onColor('warning'),

                            ])
                            // ->colStyles([
                            //     'permission_id' => 'padding-right: 30px;',
                            //     'is_viewAny' => 'width:8%; text-align:left;',
                            //     'is_view' => 'width:8%; text-align:left;',
                            //     'is_create' => 'width:8%; text-align:left;',
                            //     'is_update' => 'width:8%; text-align:left;',
                            //     'is_delete' => 'width:8%; text-align:left;',
                            //     'is_forceDelete' => 'width:8%; text-align:left;',
                            //     'is_restore' => 'width:8%; text-align:left;',
                            //     'active' => 'width:8%; text-align:left;',
                            // ])
                            ->columns(8)
                            
                            ->reorderable()
                            ->cloneable()
                            ->collapsible()
                        //->minItems(1),
                        //->maxItems(5)
                    ])->columnSpanFull()
            ]);
    }
}
