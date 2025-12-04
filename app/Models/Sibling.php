<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sibling extends Model
{
    protected $table = "siblings";
    protected $fillable = [
        'data',
    ];
    
    protected $casts = [
        'data' => 'array',
    ];
}
