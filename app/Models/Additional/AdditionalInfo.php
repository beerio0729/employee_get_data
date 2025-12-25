<?php

namespace App\Models\Additional;

use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Subdistricts;
use Illuminate\Database\Eloquent\Model;

class AdditionalInfo extends Model
{
    protected $table = "additional_infos";
    protected $fillable = [
        // Emergency contact
        'emergency_name',               // ชื่อผู้ติดต่อฉุกเฉิน  
        'emergency_relation',           // ความสัมพันธ์
        'emergency_tel',                // เบอร์โทร    
        'emergency_address',            // ที่อยู่ 
        'province_id',           // จังหวัด
        'district_id',           // อำเภอ
        'subdistrict_id',        // ตำบล
        'zipcode',            // รหัสไปรษณีย์ 
        

        // Work history with company
        'worked_company_before',       // เคยทำงานกับบริษัทมาก่อนหรือไม่ (boolean)
        'worked_company_detail',       // รายละเอียดเพิ่มเติม  
        'worked_company_supervisor',   // ชื่อหัวหน้างานตอนนั้น  

        // Know someone in company
        'know_someone',                // รู้จักใครในบริษัทหรือไม่ (boolean)
        'know_someone_name',           // ชื่อคนที่รู้จัก  
        'know_someone_relation',       // ความสัมพันธ์  

        // Job source
        'how_to_know_job',             // รู้จักงานนี้จากที่ไหน  

        // Medical condition
        'medical_condition',           // มีโรคประจำตัวหรือไม่ (boolean)
        'medical_condition_detail',    // รายละเอียดโรค  

        // Social security
        'has_sso',                     // ยังมีประกันสังคมหรือไม่ (boolean)
        'sso_hospital',                // โรงพยาบาลที่เลือก  

        // Additional info
        'additional_info',             // ข้อมูลเพิ่มเติมที่อยากแจ้ง
    ];
    
     public function additionalInfoBelongtoprovince()
    {
        return $this->belongsTo(Provinces::class, 'province_id', 'id');
    }

    public function additionalInfoBelongtodistrict()
    {
        return $this->belongsTo(Districts::class, 'district_id', 'id');
    }

    public function additionalInfoBelongtosubdistrict()
    {
        return $this->belongsTo(Subdistricts::class, 'subdistrict_id', 'id');
    }
}
