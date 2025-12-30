<?php

namespace App\Models\WorkStatus;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\WorkStatusDefination\WorkStatusDefination;

class PostEmployment extends Model
{
    protected $table = 'post_employments';

    protected $fillable = [
        'user_id',  // อ้างอิง users
        'work_status_id', //
        'employee_code',   // รหัสพนักงาน
        'level_id', //ระดับตำสุดของโครงสร้างองค์กร เช่น ตำแหน่งงาน
        'salary',
        'hired_at'       // วันที่เริ่มงาน
    ];
    
    protected $casts = [
        'hired_at'   => 'datetime',  // วันที่สมัคร
    ];

}
