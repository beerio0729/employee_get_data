<?php

namespace App\Models\Additional;

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
