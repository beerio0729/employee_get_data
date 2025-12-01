<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;

class ResumeLocationWork extends Model
{
    protected $table = "resume_location_works"; //ชื่อตาราง    
    protected $fillable = [
        'location',
        'other_location'
    ];
    
        protected $casts = [
        'location' => 'array', 
        'other_location' => 'array',
    ];
}
