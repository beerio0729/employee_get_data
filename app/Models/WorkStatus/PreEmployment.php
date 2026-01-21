<?php

namespace App\Models\WorkStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;

class PreEmployment extends Model
{
    protected $table = "pre_employments";
    protected $fillable = [
        'user_id',        // อ้างอิงไปยังตาราง users
        'work_status_id',
        'interview_channel', //ช่องทางการสัมภาษณ์
        'google_calendar_id',
        'applied_at',     // วันที่สมัคร
        'start_interview_at', 
        'end_interview_time',  // วันสัมภาษณ์ (ถ้ามี)
        'result_at',      // วันที่สรุปผล (ถ้ามี)
    ];

    protected $casts = [
        'applied_at'   => 'datetime',  // วันที่สมัคร
        'start_interview_at' => 'datetime',
        'end_interview_time' => 'datetime',
        'result_at'    => 'datetime',  // วันที่สรุปผล
    ];
}
