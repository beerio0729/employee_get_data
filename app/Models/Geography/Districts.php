<?php

namespace App\Models\Geography;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Districts extends Model
{
    use HasFactory;
    protected $table = "districts";
    protected $fillable = [
        'id',
        'province_id',
        'name_th',
        'name_en',
    ];

    public function province()
    {
        return $this->belongsTo(Provinces::class, 'province_id', 'id');
    }
}
