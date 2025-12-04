<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Father extends Model
{
    protected $table = "fathers";
    protected $fillable = [
        'name',
        'age',
        'nationality',
        'occupation',
        'company',
        'tel',
        'alive', //boolean
    ];
}
