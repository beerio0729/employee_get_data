<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;

class ResumePositionApplied extends Model
{
    protected $table = "resume_position_applieds"; //ชื่อตาราง
    protected $fillable = [
        'position',
    ];
    
        protected $casts = [
        'position' => 'array', 
    ];
}
