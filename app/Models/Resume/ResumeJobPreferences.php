<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeJobPreferences extends Model
{
    use HasFactory;

    protected $table = "resume_job_preferences";
    protected $fillable = [
        "resume_id",
        "availability_date",
        "expected_salary",
        "desired_positions", //เป็น array
    ];
    
    protected $casts = [
        'desired_positions' => 'array', 
    ];
}
