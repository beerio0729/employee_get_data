<?php

namespace App\Models\Document;

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
