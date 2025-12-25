<?php

namespace App\Models\Document\Resume;

use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Subdistricts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeLocation extends Model
{
     use HasFactory;

    protected $table = "resume_locations";
    protected $fillable = [
        'resume_id',
        'same_id_card',
        'address',
        'province_id',
        'district_id',
        'subdistrict_id',
        'zipcode',
    ];
    public function resumeBelongtoprovince()
    {
        return $this->belongsTo(Provinces::class, 'province_id', 'id');
    }

    public function resumeBelongtodistrict()
    {
        return $this->belongsTo(Districts::class, 'district_id', 'id');
    }

    public function resumeBelongtosubdistrict()
    {
        return $this->belongsTo(Subdistricts::class, 'subdistrict_id', 'id');
    }
}
