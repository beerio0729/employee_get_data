<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    protected $table = "transcripts";
    protected $fillable = [
        'prefix_name',
        'name',
        'last_name',
        'institution', //สถาบัน
        'degree',//ชื่อวุฒิการศึกษา เช่น วิศวกรรมศาสตร์บัณทิต
        'education_level', //ระดับการศึกษา เช่น ปริญญาตรี
        'faculty', //คณะ
        'major', //สาขาวิชา
        'minor', //วิชาโท
        'date_of_admission', //ปีที่เข้าศึกษา
        'date_of_graduation', //ปีที่เข้าศึกษา
        'gpa', //เกรดเฉลี่ย
        'file_path'
    ];
}
