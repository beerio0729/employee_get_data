<?php

namespace App\Models\Document\Resume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeSkills extends Model
{
    use HasFactory;

    protected $table = "resume_skills";
    protected $fillable = [
        'resume_id',
        'skill_name',
        'level',
    ];
}
