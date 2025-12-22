<?php

namespace App\Models;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'user_id',         // อ้างอิง users
        'employee_code',   // รหัสพนักงาน
        'department_id',   // แผนก
        'position_id',
        'hired_at',        // วันที่เริ่มงาน
        'status',          // สถานะการทำงาน
    ];
    
    public function employeeBelongToDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    
    public function employeeBelongToPosition()
    {
        return $this->belongsTo(Position::class, 'department_id', 'id');
    }
}
