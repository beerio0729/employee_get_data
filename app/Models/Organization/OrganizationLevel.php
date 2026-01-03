<?php

namespace App\Models\Organization;

use Illuminate\Database\Eloquent\Model;

class OrganizationLevel extends Model
{
    protected $table = 'organization_levels';
    protected $fillable = [
        'name_th',
        'name_en',
        'level',
    ];
}
