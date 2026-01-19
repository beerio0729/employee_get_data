<?php

namespace App\Filament\Panel\Admin\Overrides;

use UnitEnum;
use Detection\MobileDetect;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use App\Providers\Filament\AdminPanelProvider;
use App\Models\Organization\OrganizationStructure;
use Filament\Panel;

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
    
    public static function getSlug(?Panel $panel = null): string
    {
        return OrganizationStructure::getLevelCollection(static::$level)?->name_en ?? '-';
    }

    public static function getNavigationIcon(): ?Heroicon
    {
        $detect = new MobileDetect();
        if (($detect->isiOS() || $detect->isAndroidOS()) || !Cache::get('top_navigation_' . auth()->id()) ?? 0) {
            return null;
        } else {
            return Heroicon::EllipsisVertical;
        }
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Settings';
    }

    public static function getNavigationParentItem(): ?string
    {   
        $detect = new MobileDetect();

        if (($detect->isiOS() || $detect->isAndroidOS()) || !Cache::get('top_navigation_' . auth()->id()) ?? 0) {
            return null;
        } else {
            return 'โครงสร้างองค์กร';
        }
    }

}
