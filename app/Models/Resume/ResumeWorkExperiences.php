<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeWorkExperiences extends Model
{
    use HasFactory;

    protected $table = "resume_work_experiences";
    protected $fillable = [
        "resume_id",
        "company", //บริษัท str
        "position", //ตำแหน่ง str
        "duration", //ช่วงเวลา str
        "salary", //เงินเดือน int
        "details" //รายละเอียด textarea
    ];
}
