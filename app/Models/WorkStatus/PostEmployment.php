<?php

namespace App\Models\WorkStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostEmployment extends Model
{
    protected $table = 'post_employments';

    protected $fillable = [
        'work_status_id', //
        'position_id',
        'employee_code',   // รหัสพนักงาน
        'lowest_org_structure_id', //id ของช้อมูลในระดับต่ำสุดของโครงสร้างองค์กร ซึ่งส่วนมากจะเป็นตำแหน่ง เช่น หัวหน้าวิศวกร
        'post_employment_grade_id', //ระดับชั้นของคนๆ นั้น
        'salary',
        'hired_at'       // วันที่เริ่มงาน
    ];
    
    protected $casts = [
        'hired_at'   => 'datetime',  // วันที่สมัคร
    ];
    
    public function PostEmpBelongToGrade() :BelongsTo
    {
        return $this->belongsTo(PostEmploymentGrade::class, 'post_employment_grade_id', 'id')->withDefault();
    }

}
