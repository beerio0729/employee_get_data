<?php

namespace App\Models\Document\Resume;

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
        "positions_id",
        "location",
        "other_location",
    ];
    
    protected $casts = [
        'positions_id' => 'array',
        'location' => 'array',
        'other_location' => 'array',
         
    ];

}
