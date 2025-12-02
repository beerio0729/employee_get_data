<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Military extends Model
{
    protected $table = "militarys"; //ชื่อตาราง
    protected $fillable = [
        'id_card',
        'type', //ประเภท สด
        'result', // ผลการจับฉลาก เช่น ใบดำ สำหรับ สด 43
        'reason_for_exemption', //เหตุผลที่ได้รับการยกเว้น
        'category', //ดูความสมบูรณ์ของร่างกายตอนเกณฑ์หทาร สำหรับ สด 43
        'date_to_army', //วันที่ต้องไปเกณฑ์สำหรับคนได้ใบแดง
    ];

    protected $casts = [
        'date_to_army' => 'date', // Laravel จะแปลง String 'YYYY-MM-DD' เป็น Carbon/DateTime Object อัตโนมัติ
    ];
}
