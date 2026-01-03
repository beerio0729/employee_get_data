<?php

namespace App\Filament\Panel\Admin\Overrides;

use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Models\Organization\OrganizationStructure;

abstract class BaseOrganizationStructureResource extends Resource
{
    protected static int $level;

    public static function getModelLabel(): string
    {
        return OrganizationStructure::getLevelCollection(static::$level)?->name_th ?? '-';
    }

    public static function getNavigationLabel(): string
    {
        return OrganizationStructure::getLevelCollection(static::$level)?->name_th ?? '-';
    }

    public static function getRecordTitleAttribute(): ?string
    {
        return 'name_th';
    }

    public static function getNavigationIcon(): ?Heroicon
    {
        return Heroicon::BuildingOffice;
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'ตั้งค่าองค์กร';
    }
}
