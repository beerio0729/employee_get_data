<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    
    protected $table = "roles"; //ชื่อตาราง
    protected $fillable = [
        'name',
        'name_th',
        'active',
        'created_at'
    ];

}
