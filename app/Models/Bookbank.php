<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookbank extends Model
{
    protected $table = "bookbanks"; //ชื่อตาราง
    protected $fillable = [
        'name',
        'bank_name',
        'bank_id'
    ];
}
