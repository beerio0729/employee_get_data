<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;
    
    protected $table = "roles"; //ชื่อตาราง
    protected $fillable = [
        'id',
        'name',
        'active',
        'deleted_at',
        'created_at'
    ];

}
