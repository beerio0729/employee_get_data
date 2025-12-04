<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mother extends Model
{
    protected $table = "mothers";
    protected $fillable = [
        'name',
        'age',
        'nationality',
        'occupation',
        'company',
        'tel',
        'alive',
    ];
}
