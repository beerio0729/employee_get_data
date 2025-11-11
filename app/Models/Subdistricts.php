<?php

namespace App\Models;

use App\Models\Districts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subdistricts extends Model
{
    use HasFactory;

    protected $table = "subdistricts"; //ชื่อตาราง
    protected $fillable = [
        'id',
        'province_id',
        'district_id',
        'zipcode',
        'name_th',
        'name_en',
    ];

    public function district()
    {
        return $this->belongsTo(Districts::class, 'district_id', 'id');
    }
}
