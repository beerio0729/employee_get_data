<?php

namespace App\Models\Organization;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class OrganizationStructure extends Model
{
    protected $table = 'organization_structures';
    protected $fillable = [
        'name_th',
        'name_en',
        'parent_id',
        'organization_level_id',
        'max_count', //จำนวนสูงสุดที่อนุญาติ
        'code'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    
    protected static function getLevelCollection(int $level): OrganizationLevel
    {
        return Cache::remember(
            'org_level_collection_' . $level,
            604800,
            function () use ($level){
                return OrganizationLevel::where('level', $level)->first();
            }
        );
    }

    protected static function getLevelId(int $level): ?int
    {
        return Cache::remember(
            'org_level_id_' . $level,
            604800,
            fn() => OrganizationLevel::where('level', $level)->value('id')
        );
    }
    
    protected static function getLevelLowest(): ?int
    {
        return Cache::remember(
            'org_level_lowest',
            604800,
            fn() => OrganizationLevel::max('level')
        );
    }
}
