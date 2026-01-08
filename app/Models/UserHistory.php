<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    protected $table = "user_histories";
    protected $fillable = [
        'data',
    ];
    
    protected $casts = [
        'data' => 'array',
    ];
}
