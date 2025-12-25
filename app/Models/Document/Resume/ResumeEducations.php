<?php

namespace App\Models\Document\Resume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeEducations extends Model
{
    use HasFactory;

    protected $table = "resume_educations";
    protected $fillable = [
        'resume_id',
        'institution', //สถาบัน
        'degree',//ชื่อวุฒิการศึกษา เช่น วิศวกรรมศาสตร์บัณทิต
        'education_level', //ระดับการศึกษา เช่น ปริญญาตรี
        'faculty', //คณะ
        'major', //สาขาวิชา
        'start_year', //ปีที่เข้าศึกษา
        'last_year', //ปีที่เข้าศึกษา
        'gpa', //เกรดเฉลี่ย
    ];
}
