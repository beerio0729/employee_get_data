<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = [
        'department_id', // แผนก
        'name',          // ชื่อตำแหน่ง
        'sort_order',    // ลำดับการแสดงผล
        'is_active',     // ใช้งาน / ไม่ใช้งาน
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];
}
