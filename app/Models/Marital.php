<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marital extends Model
{
    protected $table = "militarys"; //ชื่อตาราง
    protected $fillable = [
        'type', //ใบหย่า หรือ ใบสมรส
        'registration_number', //เลขทะเบียนเอกสาร
        'man',
        'women',
        'issue_date' //วันออกเอกสาร
    ];
    
    protected $casts = [
        'issue_date' => 'date', // Laravel จะแปลง String 'YYYY-MM-DD' เป็น Carbon/DateTime Object อัตโนมัติ
    ];
}
