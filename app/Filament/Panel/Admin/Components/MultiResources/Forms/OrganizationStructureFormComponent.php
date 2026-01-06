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

    public static function formSecondComponent(): Select
    {
        return
            Select::make('parent_id')
            ->reactive()
            ->searchable()
            ->label(
                fn() =>
                'เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th
            )
            ->options(
                OrganizationStructure::where('organization_level_id', OrganizationStructure::getLevelId(1))->pluck('name_th', 'id')
            );
    }

    public static function formThirdComponent(): array
    {
        return [
            Select::make('first_id')
                ->label(
                    'เลือก' . OrganizationStructure::getLevelCollection(1)?->name_th
                )
                ->options(
                    OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId(1))->pluck('name_th', 'id')
                )
                ->reactive(),

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
                ->afterStateHydrated(function ($state, $set) {
                    if (! $state) return;

                    $level2 = OrganizationStructure::with('parent')->find($state);
                    if (! $level2?->parent) return;
                    $set('first_id', $level2->parent->id);
                })
                ->disabled(fn(callable $get) => ! $get('first_id'))
        ];
    }

    public static function formFourthComponent(): array
    {
        return [
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
                ->afterStateHydrated(function ($state, $set) {
                    if (! $state) return;

                    $level3 = OrganizationStructure::with('parent.parent')->find($state);

                    if (! $level3?->parent?->parent) return;
                    $set('second_id', $level3->parent->id);
                    $set('first_id', $level3->parent->parent->id);
                })
                ->reactive()
                ->disabled(fn($get) => ! $get('second_id')),
        ];
    }

    public static function formFifthComponent(): array
    {
        return [
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
                ->reactive()
                ->afterStateHydrated(function ($state, $set) {
                    if (! $state) return;

                    $level4 = OrganizationStructure::with('parent.parent.parent')->find($state);

                    if (! $level4?->parent?->parent?->parent) return;
                    $set('third_id', $level4->parent->id);
                    $set('second_id', $level4->parent->parent->id);
                    $set('first_id', $level4->parent->parent->parent->id);
                })
                ->disabled(fn($get) => ! $get('third_id')),
        ];
    }

    public static function formSixthComponent(): array
    {
        return [
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
                    : [])
                ->afterStateHydrated(function ($state, $set) {
                    if (! $state) return;

                    $level5 = OrganizationStructure::with('parent.parent.parent.parent')->find($state);

                    if (! $level5->parent->parent->parent->parent) return;

                    $set('fourth_id', $level5->parent->id);
                    $set('third_id',  $level5->parent->parent?->id);
                    $set('second_id', $level5->parent->parent->parent?->id);
                    $set('first_id',  $level5->parent->parent->parent->parent?->id);
                })
                ->reactive()
                ->disabled(fn($get) => ! $get('fourth_id')),
        ];
    }

    public static function formSeventhComponent(): array
    {
        return [
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
                        ->where('parent_id', $get('fifth_id'))
                        ->pluck('name_th', 'id')
                        : []
                )
                ->afterStateHydrated(function ($state, $set) {
                    if (! $state) return;

                    $level6 = OrganizationStructure::with('parent.parent.parent.parent.parent')->find($state);

                    if (! $level6->parent->parent->parent->parent->parent) return;

                    $set('fifth_id',  $level6->parent->id);
                    $set('fourth_id', $level6->parent->parent?->id);
                    $set('third_id',  $level6->parent->parent->parent?->id);
                    $set('second_id', $level6->parent->parent->parent->parent?->id);
                    $set('first_id',  $level6->parent->parent->parent->parent->parent?->id);
                })
                ->reactive()
                ->disabled(fn($get) => ! $get('fifth_id')),
        ];
    }
}
