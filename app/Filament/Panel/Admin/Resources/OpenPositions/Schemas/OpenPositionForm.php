<?php

namespace App\Filament\Panel\Admin\Resources\OpenPositions\Schemas;

use Dom\Text;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Organization\OrganizationStructure;

class OpenPositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                function () {
                    $lowest_level = OrganizationStructure::getLevelLowest();
                    $map = [
                        1 => 'First',
                        2 => 'Second',
                        3 => 'Third',
                        4 => 'Fourth',
                        5 => 'Fifth',
                        6 => 'Sixth',
                        7 => 'Seventh',
                    ];
                    $lowest_level_text = $map[$lowest_level] ?? null;
                    $class = "App\\Filament\\Panel\\Admin\\Components\\MultiResources\\Forms\\OrganizationStructureFormComponent";
                    $methode = "form{$lowest_level_text}Component";
                    return [
                        ...$class::$methode(),
                        Select::make('position_id')
                            ->searchable()
                            ->reactive()
                            ->label(
                                function () use ($lowest_level) {
                                    return 'เลือก' . OrganizationStructure::getLevelCollection($lowest_level)?->name_th;
                                }
                            )
                            ->options(
                                function ($get, $model) use ($lowest_level) {
                                    $openPositionIds = $model::pluck('position_id')->toArray();
                                    $org_level = OrganizationStructure::where('organization_level_id', organizationStructure::getLevelId($lowest_level))
                                        ->whereNotIn('id', $openPositionIds);
                                    return
                                        $get("parent_id") //ถ้ามีการกรองข้อมูล
                                        ? $org_level->where('parent_id', $get("parent_id"))->pluck('name_th', 'id') //เลือกเฉพาะส่วนที่กรอง
                                        : $org_level->pluck('name_th', 'id'); //เอาทั้งหมด
                                }

                            )->afterStateHydrated(function ($state, $set) use ($lowest_level) {
                                if (! $state) return;

                                $org_structure = OrganizationStructure::find($state);
                                if (! $org_structure) return;

                                // map level -> field name
                                $levelMap = [
                                    1 => 'first_id',
                                    2 => 'second_id',
                                    3 => 'third_id',
                                    4 => 'fourth_id',
                                    5 => 'fifth_id',
                                    6 => 'sixth_id',
                                    7 => 'seventh_id',
                                ];

                                $current = $org_structure?->parent;
                                $currentLevel = $lowest_level - 1;

                                // ตั้ง parent_id (level ก่อนหน้า position)
                                if ($current) {
                                    $set('parent_id', $current->id);
                                }

                                // ไล่ย้อนขึ้นไปเรื่อย ๆ
                                while ($currentLevel >= 1) {
                                    $set($levelMap[$currentLevel], $current->id);
                                    $current = $current?->parent;
                                    $currentLevel--;
                                }
                            })
                    ];
                },

            )->columns(3);
    }
}
