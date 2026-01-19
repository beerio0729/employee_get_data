<?php

namespace App\Models\WorkStatus;

use Illuminate\Database\Eloquent\Model;
use App\Models\Organization\OrganizationStructure;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostEmployment extends Model
{
    protected $table = 'post_employments';

    protected $fillable = [
        'work_status_id',
        'manager_id',
        'employee_code',   // รหัสพนักงาน
        'lowest_org_structure_id', //id ของช้อมูลในระดับต่ำสุดของโครงสร้างองค์กร ซึ่งส่วนมากจะเป็นตำแหน่ง เช่น หัวหน้าวิศวกร
        'post_employment_grade_id', //ระดับชั้นของคนๆ นั้น
        'salary',
        'hired_at'       // วันที่เริ่มงาน
    ];

    protected $casts = [
        'hired_at'   => 'datetime',  // วันที่สมัคร
    ];

    public function postEmpBelongToGrade(): BelongsTo
    {
        return $this->belongsTo(PostEmploymentGrade::class, 'post_employment_grade_id', 'id')->withDefault();
    }

    public function postEmpBelongToOrg(): BelongsTo
    {
        return $this->belongsTo(OrganizationStructure::class, 'lowest_org_structure_id', 'id')->withDefault();
    }
}
