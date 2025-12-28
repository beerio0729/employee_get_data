<?php

namespace App\Filament\Panel\Admin\Overrides;

use Filament\Resources\Resource;
use App\Models\OrganizationLevel;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

abstract class BaseOrganizationStructureResource extends Resource
{
    protected static int $level;

    protected static function level(): ?OrganizationLevel
    {   
        return cache()->remember(
            'org_level_' . static::$level,
            3600,
            fn () => OrganizationLevel::where('level', static::$level)->first()
        );
    }

    public static function getModelLabel(): string
    {
        return static::level()?->name_th ?? '-';
    }

    public static function getNavigationLabel(): string
    {   
        return static::level()?->name_th ?? '-';
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
