<?php

namespace App\Models\WorkStatus;

use Illuminate\Database\Eloquent\Model;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkStatus extends Model
{
    protected $table = 'work_statuses';
    protected $fillable = [
        'user_id',
        'work_status_def_detail_id',
    ];

    public function workStatusBelongToWorkStatusDefDetail() :BelongsTo
    {
        return $this->belongsTo(WorkStatusDefinationDetail::class, 'work_status_def_detail_id', 'id')->withDefault();
    }

    public function workStatusHasonePreEmp() :HasOne
    {
        return $this->hasOne(PreEmployment::class, 'work_status_id', 'id')->withDefault();
    }
    
    public function workStatusHasonePostEmp() :HasOne
    {
        return $this->hasOne(PostEmployment::class, 'work_status_id', 'id')->withDefault();
    }
}
