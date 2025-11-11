<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Idcard extends Model
{   
    use HasFactory;
    
    protected $table = "id_cards"; //ชื่อตาราง
    protected $fillable = [
        'user_id',
        'prefix_name_th',
        'name_th',
        'last_name_th',
        'prefix_name_en',
        'name_en',
        'last_name_en',
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
}
