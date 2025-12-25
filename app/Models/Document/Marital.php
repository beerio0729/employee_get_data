<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class Marital extends Model
{
    protected $table = "maritals"; //ชื่อตาราง
    protected $fillable = [
        'status',// สถานนะ
        'type', //ใบหย่า หรือ ใบสมรส
        'registration_number', //เลขทะเบียนเอกสาร
        'man',
        'woman',
        'issue_date', //วันออกเอกสาร
        
        /*****ข้อมูลเพิ่มเติมนอกเอกสาร*****/
        
        'age', //อายุคู่สมรส
        'alive', //มีชีวิตอยู่ไหม เป็น boolean
        'occupation',
        'company',
        'no_of_children', //จำนวนลูก int
        'male', //จำนวนลูกชาย int
        'female', //จำนวนลูกสาว int
        
    ];
    
    protected $casts = [
        'issue_date' => 'date', // Laravel จะแปลง String 'YYYY-MM-DD' เป็น Carbon/DateTime Object อัตโนมัติ
    ];
}
