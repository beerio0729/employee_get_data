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
        "start", //ช่วงเวลาเริ่มต้น
        "last",
        "salary", //เงินเดือน int
        "details", //รายละเอียด textarea
        "reason_for_leaving" //เหตุผลที่ลาออก
    ];
}
