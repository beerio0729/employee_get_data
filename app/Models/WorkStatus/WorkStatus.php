<?php

namespace App\Models\WorkStatus;

use Illuminate\Database\Eloquent\Model;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;

class WorkStatus extends Model
{
    protected $table = 'work_statuses';
    protected $fillable = [
        'user_id',
        'work_status_def_detail_id',
    ];

    public function workStatusBelongToWorkStatusDefDetail() //resume
    {
        return $this->belongsTo(WorkStatusDefinationDetail::class, 'work_status_def_detail_id', 'id')->withDefault();
    }

    public function workStatusHasonePreEmp() //resume
    {
        return $this->hasOne(PreEmployment::class, 'work_status_id', 'id')->withDefault();
    }
    
    public function workStatusHasmanyPreEmp() //resume
    {
        return $this->hasMany(PostEmployment::class, 'work_status_id', 'id')->withDefault();
    }
}
