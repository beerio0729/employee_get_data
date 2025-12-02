<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;
use App\Models\Resume\ResumeLocationWork;
use App\Models\Resume\ResumePositionApplied;
// use App\Models\Resume\ResumeSkills;
// use App\Models\Resume\ResumeLocation;
// use App\Models\Resume\ResumeEducations;
// use App\Models\Resume\ResumeLangSkills;
// use App\Models\Resume\ResumeCertificates;
// use App\Models\Resume\ResumeOtherContacts;
// use App\Models\Resume\ResumeJobPreferences;
// use App\Models\Resume\ResumeWorkExperiences;

class Resume extends Model
{
    protected $table = "resumes";
    protected $fillable = [
        'prefix_name',
        'name',
        'last_name',
        'tel',
        'date_of_birth',
        'marital_status',
        'id_card',
        'gender',
        'height',
        'weight',
        'military', //เกณฑ์หทาร
        'nationality', //สัญชาติ
        'religion', //ศาสนา
        'image',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];
    
    public function resumeHasonelocation()
    {
        return $this->hasOne(ResumeLocation::class, 'resume_id', 'id');
    }

    public function resumeHasoneJobPreference()
    {
        return $this->hasOne(ResumeJobPreferences::class, 'resume_id', 'id');
    }

    public function resumeHasmanyEducation()
    {
        return $this->hasMany(ResumeEducations::class, 'resume_id', 'id');
    }

    public function resumeHasmanyWorkExperiences()
    {
        return $this->hasMany(ResumeWorkExperiences::class, 'resume_id', 'id');
    }

    public function resumeHasmanyLangSkill()
    {
        return $this->hasMany(ResumeLangSkills::class, 'resume_id', 'id');
    }

    public function resumeHasmanySkill()
    {
        return $this->hasMany(ResumeSkills::class, 'resume_id', 'id');
    }

    public function resumeHasmanyCertificate()
    {
        return $this->hasMany(ResumeCertificates::class, 'resume_id', 'id');
    }

    public function resumeHasmanyOtherContact()
    {
        return $this->hasMany(ResumeOtherContacts::class, 'resume_id', 'id');
    }
}
