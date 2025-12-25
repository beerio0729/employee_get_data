<?php

namespace App\Models\Additional;

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
