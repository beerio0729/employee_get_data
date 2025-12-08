<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = "certificates"; //ชื่อตาราง
    protected $fillable = [
        'data',
    ];
    
    protected $casts = [
        'data' => 'array',
    ];
}
