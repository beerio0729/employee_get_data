<?php

namespace App\Models;

use App\Models\Geography\Districts;
use App\Models\Geography\Provinces;
use App\Models\Geography\Subdistricts;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $table = 'company_profiles'; //ชื่อตาราง
    protected $fillable = [
        // เดิม (ไม่แก้)
        'name',
        'address',
        'province_id',
        'district_id',
        'subdistrict_id',
        'zipcode',

        // เพิ่ม (จำเป็นจริง)
        'company_type',                 // บจก., หจก., บมจ.
        'tax_id',                       // เลขผู้เสียภาษี 13 หลัก

        'authorized_person_name',       // ผู้มีอำนาจลงนาม
        'authorized_person_position',   // ตำแหน่ง

        'phone',                        // ติดต่อ
        'email',
    ];
    
    public function companyBelongtoProvince()
    {
        return $this->belongsTo(Provinces::class, 'province_id', 'id');
    }

    public function companyBelongtoDistrict()
    {
        return $this->belongsTo(Districts::class, 'district_id', 'id');
    }

    public function companyBelongtoSubdistrict()
    {
        return $this->belongsTo(Subdistricts::class, 'subdistrict_id', 'id');
    }
}
