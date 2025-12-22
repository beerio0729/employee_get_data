<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = "applicants"; //ชื่อตาราง
    protected $fillable = [
        'user_id',        // อ้างอิงไปยังตาราง users
        'status',         // สถานะการสมัคร (draft, completed, interview_scheduled, interviewed, passed, rejected)
        'applied_at',     // วันที่สมัคร
        'interview_at',   // วันสัมภาษณ์ (ถ้ามี)
        'result_at',      // วันที่สรุปผล (ถ้ามี)
    ];

    protected $casts = [
        'applied_at'   => 'datetime',  // วันที่สมัคร
        'interview_at' => 'datetime',  // วันสัมภาษณ์
        'result_at'    => 'datetime',  // วันที่สรุปผล
    ];
}
