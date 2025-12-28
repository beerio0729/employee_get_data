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
        'status_id',  // สถานะ
        'employee_code',   // รหัสพนักงาน
        'department_id',   // แผนก
        'position_id',
        'salary',
        'hired_at'       // วันที่เริ่มงาน
    ];
    
    protected $casts = [
        'hired_at'   => 'datetime',  // วันที่สมัคร
    ];

    public function postEmploymentBelongToWorkStatusDefination(): BelongsTo
    {
        return $this->belongsTo(WorkStatusDefination::class, 'status_id', 'id');
    }

    public function employeeBelongToDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function employeeBelongToPosition()
    {
        return $this->belongsTo(Position::class, 'department_id', 'id');
    }
}
