<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeLangSkills extends Model
{
    use HasFactory;

    protected $table = "resume_lang_skills";
    protected $fillable = [
        "resume_id",
        "language",
        "speaking",
        "listening",
        "writing"
    ];
}
