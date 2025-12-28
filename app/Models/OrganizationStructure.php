<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationStructure extends Model
{
    protected $table = 'organization_structures';
    protected $fillable = [
        'name_th',
        'name_en',
        'parent_id',
        'type',
        'level',
        'code'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
