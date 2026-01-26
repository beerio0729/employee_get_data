<?php

namespace App\Models\WorkStatusDefination;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkStatusDefinationDetail extends Model
{
    protected $table = "work_status_defination_details";

    protected $fillable = [
        'work_status_def_id',
        'code',
        'work_phase', // (string / enum) เก็บช่วงของเหตุการณ์เช่น ก่อนเวลานัดสัมพาด นัดสัมภาษณ์แล้ว หลังเวลาสัมพาด หลังเวลาประกาศผล เป็นต้น
        'name_th',
        'name_en',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workStatusDefDetailBelongsToWorkStatusDef(): BelongsTo
    {
        return $this->belongsTo(WorkStatusDefination::class, 'work_status_def_id', 'id');
    }

    public static function statusId($status): int
    {
        return static::where('code', $status)->value('id');
    }

    public static function workPhase($id, $phase): bool
    {
        return blank($id) || blank($phase)
            ? false
            : static::find($id)->work_phase === $phase;
    }
}
