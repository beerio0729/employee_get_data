<?php

namespace App\Models;

use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Subdistricts;
use Illuminate\Database\Eloquent\Model;

class Idcard extends Model
{  
    protected $table = "id_cards"; //ชื่อตาราง
    protected $fillable = [
        'user_id',
        'prefix_name_th',
        'name_th',
        'last_name_th',
        'prefix_name_en',
        'name_en',
        'last_name_en',
        'gender',
        'id_card_number',
        'religion', //ศาสนา
        'date_of_birth',
        'address',
        'province_id',
        'district_id',
        'subdistrict_id',
        'zipcode',
        'date_of_issue', //วันที่ออกบัตร
        'date_of_expiry', //วันบัตรหมดอายุ
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_issue'=> 'date', //วันที่ออกบัตร
        'date_of_expiry'=> 'date',
    ];
    
    
    /***************ที่อยู่ตามบัตร*************** */
    
    public function idcardBelongtoprovince()
    {
        return $this->belongsTo(Provinces::class, 'province_id', 'id');
    }

    public function idcardBelongtodistrict()
    {
        return $this->belongsTo(Districts::class, 'district_id', 'id');
    }

    public function idcardBelongtosubdistrict()
    {
        return $this->belongsTo(Subdistricts::class, 'subdistrict_id', 'id');
    }
}
