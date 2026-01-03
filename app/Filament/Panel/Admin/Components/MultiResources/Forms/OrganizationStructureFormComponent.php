<?php

namespace App\Filament\Panel\Admin\Components\MultiResources\Forms;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use App\Models\Organization\OrganizationLevel;
use App\Models\Organization\OrganizationStructure;


class OrganizationStructureFormComponent
{

    public static function formComponent($label, $level): array
    {
        $isLowest = $level === OrganizationStructure::getLevelLowest();
        return [
            TextInput::make('name_th')->label("ชื่อ{$label}")->disabled(fn($get) => ! $get('parent_id')),
            TextInput::make('name_en')->label("ชื่อ{$label} (En)")->disabled(fn($get) => ! $get('parent_id')),
            TextInput::make('code')->label('Code เช่น hr')->disabled(fn($get) => ! $get('parent_id')),
            TextInput::make('max_count')->label('จำนวนสูงสุด')->visible($isLowest), // 🔑 แสดงเฉพาะ lowest level
            Hidden::make('organization_level_id')
                ->default(function () use ($level) {
                    return OrganizationLevel::where('level', $level)->value('id');
                })

        ];
    }

    public static function formFirstComponent($label, $level): array
    {
        return [
            TextInput::make('name_th')->label("ชื่อ{$label}"),
            TextInput::make('name_en')->label("ชื่อ{$label} (En)"),
            TextInput::make('code')->label('Code เช่น hr'),
            Hidden::make('organization_level_id')
                ->default(function () use ($level) {
                    return OrganizationLevel::where('level', $level)->value('id');
                })
        ];
    }

    public static function formSecondComponent($label, $level): array
    {
        return [
            Select::make('parent_id')
                ->reactive()
                ->searchable()
                ->label(
                    fn() =>
                    'เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th
                )
                ->options(
                    OrganizationStructure::where('organization_level_id', OrganizationStructure::getLevelId(1))->pluck('name_th', 'id')
                ),
            ...self::formComponent($label, $level),
        ];
    }

    public static function formThirdComponent($label, $level): array
    {
        return [
            Fieldset::make('third_option')
                ->label('กรองข้อมูลองค์กร')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('first_id')
                        ->label(
                            'เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th
                        )
                        ->options(
                            OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(1))->pluck('name_th', 'id')
                        )
                        ->reactive()
                        ->dehydrated(false), // ⭐ สำคัญมาก

                    // เลือก parent (Level 2)
                    Select::make('parent_id')
                        ->searchable()
                        ->reactive()
                        ->label(
                            'เลือก' . OrganizationStructure::getLevelCollection(2)?->name_th
                        )
                        ->options(
                            fn(callable $get) =>
                            $get('first_id')
                                ? OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(2))
                                ->where('parent_id', $get('first_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->disabled(fn(callable $get) => ! $get('first_id')),
                ]),

            Fieldset::make('third_fill')
                ->label('กรอกข้อมูล')
                ->columns(3)
                ->columnSpanFull()
                ->schema(
                    self::formComponent($label, $level)
                )
        ];
    }

    public static function formFourthComponent($label, $level): array
    {
        return [
            Fieldset::make('fourth_option')
                ->label('กรองข้อมูลองค์กร')
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    Select::make('first_id')
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th)
                        ->options(
                            OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(1))->pluck('name_th', 'id')
                        )
                        ->reactive()
                        ->dehydrated(false),

                    Select::make('second_id')
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(2)?->name_th)
                        ->options(
                            fn($get) =>
                            $get('first_id')
                                ? OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(2))
                                ->where('parent_id', $get('first_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->reactive()
                        ->dehydrated(false)
                        ->disabled(fn($get) => ! $get('first_id')),

                    Select::make('parent_id')
                        ->searchable()
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(3)?->name_th)
                        ->options(
                            fn($get) =>
                            $get('second_id')
                                ? OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(3))
                                ->where('parent_id', $get('second_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->disabled(fn($get) => ! $get('second_id')),
                ]),

            Fieldset::make('fourth_fill')
                ->label('กรอกข้อมูล')
                ->columns(3)
                ->columnSpanFull()
                ->schema(self::formComponent($label, $level)),
        ];
    }

    public static function formFifthComponent($label, $level): array
    {
        return [
            Fieldset::make('fifth_option')
                ->label('กรองข้อมูลองค์กร')
                ->columnSpanFull()
                ->columns(4)
                ->schema([
                    Select::make('first_id')->label('เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th)
                        ->options(OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(1))->pluck('name_th', 'id'))
                        ->reactive()->dehydrated(false),

                    Select::make('second_id')->label('เลือก' . OrganizationStructure::getLevelCollection(2)?->name_th)
                        ->options(fn($get) => $get('first_id')
                            ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(2))->where('parent_id', $get('first_id'))->pluck('name_th', 'id')
                            : [])
                        ->reactive()->dehydrated(false)
                        ->disabled(fn($get) => ! $get('first_id')),

                    Select::make('third_id')->label('เลือก' . OrganizationStructure::getLevelCollection(3)?->name_th)
                        ->options(fn($get) => $get('second_id')
                            ? OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(3))->where('parent_id', $get('second_id'))->pluck('name_th', 'id')
                            : [])
                        ->reactive()->dehydrated(false)
                        ->disabled(fn($get) => ! $get('second_id')),

                    Select::make('parent_id')->searchable()
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(4)?->name_th)
                        ->options(fn($get) => $get('third_id')
                            ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(4))->where('parent_id', $get('third_id'))->pluck('name_th', 'id')
                            : [])
                        ->disabled(fn($get) => ! $get('third_id')),
                ]),

            Fieldset::make('fifth_fill')
                ->label('กรอกข้อมูล')
                ->columns(3)
                ->columnSpanFull()
                ->schema(self::formComponent($label, $level)),
        ];
    }

    public static function formSixthComponent($label, $level): array
    {
        return [
            Fieldset::make('sixth_option')
                ->label('กรองข้อมูลองค์กร')
                ->columnSpanFull()
                ->columns(4)
                ->schema([
                    Select::make('first_id')->label('เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th)
                        ->options(OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(1))->pluck('name_th', 'id'))
                        ->reactive()->dehydrated(false),

                    Select::make('second_id')->label('เลือก' . OrganizationStructure::getLevelCollection(2)?->name_th)
                        ->options(fn($get) => $get('first_id')
                            ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(2))->where('parent_id', $get('first_id'))->pluck('name_th', 'id')
                            : [])
                        ->reactive()->dehydrated(false),

                    Select::make('third_id')->label('เลือก' . OrganizationStructure::getLevelCollection(3)?->name_th)
                        ->options(fn($get) => $get('second_id')
                            ? OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(3))->where('parent_id', $get('second_id'))->pluck('name_th', 'id')
                            : [])
                        ->reactive()->dehydrated(false),

                    Select::make('fourth_id')->label('เลือก' . OrganizationStructure::getLevelCollection(4)?->name_th)
                        ->options(fn($get) => $get('third_id')
                            ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(4))->where('parent_id', $get('third_id'))->pluck('name_th', 'id')
                            : [])
                        ->reactive()->dehydrated(false),

                    Select::make('parent_id')->searchable()
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(5)?->name_th)
                        ->options(fn($get) => $get('fourth_id')
                            ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(5))->where('parent_id', $get('fourth_id'))->pluck('name_th', 'id')
                            : []),
                ]),

            Fieldset::make('sixth_fill')
                ->label('กรอกข้อมูล')
                ->columns(3)
                ->columnSpanFull()
                ->schema(self::formComponent($label, $level)),
        ];
    }

    public static function formSeventhComponent($label, $level): array
    {
        return [
            Fieldset::make('seventh_option')
                ->label('กรองข้อมูลองค์กร')
                ->columnSpanFull()
                ->columns(4)
                ->schema([
                    Select::make('first_id')
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th)
                        ->options(
                            OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(1))->pluck('name_th', 'id')
                        )
                        ->reactive()
                        ->dehydrated(false),

                    Select::make('second_id')
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(2)?->name_th)
                        ->options(
                            fn($get) =>
                            $get('first_id')
                                ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(2))
                                ->where('parent_id', $get('first_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->reactive()
                        ->dehydrated(false)
                        ->disabled(fn($get) => ! $get('first_id')),

                    Select::make('third_id')
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(3)?->name_th)
                        ->options(
                            fn($get) =>
                            $get('second_id')
                                ? OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(3))
                                ->where('parent_id', $get('second_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->reactive()
                        ->dehydrated(false)
                        ->disabled(fn($get) => ! $get('second_id')),

                    Select::make('fourth_id')
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(4)?->name_th)
                        ->options(
                            fn($get) =>
                            $get('third_id')
                                ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(4))
                                ->where('parent_id', $get('third_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->reactive()
                        ->dehydrated(false)
                        ->disabled(fn($get) => ! $get('third_id')),

                    Select::make('fifth_id')
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(5)?->name_th)
                        ->options(
                            fn($get) =>
                            $get('fourth_id')
                                ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(5))
                                ->where('parent_id', $get('fourth_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->reactive()
                        ->dehydrated(false)
                        ->disabled(fn($get) => ! $get('fourth_id')),

                    Select::make('parent_id')
                        ->searchable()
                        ->label('เลือก' . OrganizationStructure::getLevelCollection(6)?->name_th)
                        ->options(
                            fn($get) =>
                            $get('sixth_id')
                                ? OrganizationStructure::where('organization_level_id', organizationstructure::getlevelid(6))
                                ->where('parent_id', $get('sixth_id'))
                                ->pluck('name_th', 'id')
                                : []
                        )
                        ->disabled(fn($get) => ! $get('sixth_id')),
                ]),

            Fieldset::make('seventh_fill')
                ->label('กรอกข้อมูล')
                ->columns(3)
                ->columnSpanFull()
                ->schema(self::formComponent($label, $level)),
        ];
    }
}
