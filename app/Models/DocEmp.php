<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocEmp extends Model
{   
    use HasFactory;

    protected $table = "doc_emps";
    protected $fillable = [
        "user_id",
        "file_name",
        "path",
        "confirm",
        "check"
    ];
    
    protected $casts = [
        // กำหนดให้ Laravel แปลง 'path' (ใน DB เป็น JSON) ให้เป็น PHP Array โดยอัตโนมัติ
        'path' => 'array', 
    ];
}
