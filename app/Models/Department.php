<?php

namespace App\Models;

use App\Models\Position;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',       // ชื่อแผนก
        'is_active',  // ใช้งาน / ไม่ใช้งาน
    ];

    protected $casts = [
        'is_active' => 'boolean', // สถานะแผนก
    ];

    public function userHasmanyPosition() //เอกสารเพิ่มเติม
    {
        return $this->hasMany(Position::class, 'department_id', 'id');
    }
}
