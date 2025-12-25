<?php

namespace App\Models\WorkStatusDefination;

use Illuminate\Database\Eloquent\Model;

class WorkStatusDefination extends Model
{
    protected $table = "work_status_definations";
    protected $fillable = [
        'company_id',
        'main_work_status', //(pre_employment | employed)
        'code',
        'name_th', //ใช้เก็บประมาณว่า เป็นพนักงาน นักศึกษาฝึกงาน คนสมัคร เป็นเอาทซอรด์
        'name_en',
        'sequence',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function workStatusDefinationHasmanyWorkStatusDefinationDetail()
    {
        return $this->hasMany(WorkStatusDefination::class, 'work_status_def_id', 'id');
    }
}
