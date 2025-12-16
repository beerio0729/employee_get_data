<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;

use App\Models\DocEmp;
use App\Models\Father;
use App\Models\Idcard;
use App\Models\Mother;
use App\Models\Marital;
use App\Models\Sibling;
use App\Models\Bookbank;
use App\Models\Military;
use App\Models\AnotherDoc;
use App\Models\Transcript;
use App\Models\Certificate;
use App\Models\Resume\Resume;
use App\Models\AdditionalInfo;
use App\Models\Resume\ResumeSkills;
use App\Models\Resume\ResumeLocation;
use App\Models\Resume\ResumeEducations;
use App\Models\Resume\ResumeLangSkills;
use Illuminate\Notifications\Notifiable;
use App\Models\Resume\ResumeCertificates;
use App\Models\Resume\ResumeLocationWork;
use App\Models\Resume\ResumeOtherContacts;
use App\Models\Resume\ResumeJobPreferences;
use App\Models\Resume\ResumePositionApplied;
use App\Models\Resume\ResumeWorkExperiences;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role_id',
        'email',
        'tel',
        'provider',
        'provider_id',
        'password',
        'interview_date', //วันที่สัมภาษณ์
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'interview_date' => 'datetime',
        ];
    }

    /*เอาค่าของประสบการณ์มาเรียงเป็น HTML */

    public function getWorkExperienceSummaryAttribute()
    {
        if (empty($this->userHasmanyResume)) {
            return null;
        } elseif (empty($this->userHasmanyResumeToWorkExperiences)) {
            return null;
        }
        $summary = $this->userHasmanyResumeToWorkExperiences
            ->map(function ($experience) {
                // จัดรูปแบบให้แต่ละ Field ขึ้นบรรทัดใหม่ด้วยแท็ก <br>
                $output = "<br><B>บริษัท</B>: {$experience->company}<br>";
                $output .= "ตำแหน่ง: {$experience->position}<br>";
                $output .= "ช่วงเวลา: {$experience->duration}<br>";
                $output .= "เงินเดือน: {$experience->salary}<br>";
                $output .= "รายละเอียด: {$experience->details}";

                return $output;
            })
            // ใช้ implode เพื่อแทรกตัวคั่นที่ชัดเจนระหว่างแต่ละประสบการณ์ (---) ด้วยแท็ก <br>
            ->implode("<br>---------------------------");

        // คืนค่าเป็น HTML String (อย่าลืมใช้ ->html() ใน Filament TextColumn)
        return $summary;
    }

    public function age(): Attribute
    {
        return Attribute::make(
            get: function () {
                $data = $this->userHasoneResume;

                if (! $data || ! $data->date_of_birth) {
                    return 'กรุณาระบุวันเกิด'; // หรือ 0, หรือค่า default อื่นๆ ตามต้องการ
                }

                return Carbon::parse($data->date_of_birth)->age . ' ปี';
            }
        );
    }

    public function ageidcard(): Attribute
    {
        return Attribute::make(
            get: function () {
                $data = $this->userHasoneIdcard;

                if (! $data || ! $data->date_of_birth) {
                    return 'กรุณาระบุวันเกิด'; // หรือ 0, หรือค่า default อื่นๆ ตามต้องการ
                }

                return 'อายุ ' . Carbon::parse($data->date_of_birth)->age . ' ปี';
            }
        );
    }

    public function userBelongToRole()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function userHasoneResume() //resume
    {
        return $this->hasOne(Resume::class, 'user_id', 'id')->withDefault();
    }

    public function userHasoneIdcard() //บัตประชาชน
    {
        return $this->hasOne(Idcard::class, 'user_id', 'id')->withDefault();
    }

    public function userHasmanyTranscript() //วุฒิ
    {
        return $this->hasMany(Transcript::class, 'user_id', 'id');
    }

    public function userHasoneMilitary() //ทหาร
    {
        return $this->hasOne(Military::class, 'user_id', 'id');
    }

    public function userHasoneMarital() // แต่งงาน
    {
        return $this->hasOne(Marital::class, 'user_id', 'id');
    }
    
    public function userHasoneAdditionalInfo() 
    {
        return $this->hasOne(AdditionalInfo::class, 'user_id', 'id')->withDefault();
    }
    
    public function userHasoneCertificate() 
    {
        return $this->hasOne(Certificate::class, 'user_id', 'id')->withDefault();
    }

    public function userHasmanyAnotherDoc() //เอกสารเพิ่มเติม
    {
        return $this->hasMany(AnotherDoc::class, 'user_id', 'id');
    }

    public function userHasmanyDocEmp() //ไฟล์เอกสารต่างๆ
    {
        return $this->hasMany(DocEmp::class, 'user_id', 'id');
    }
    
    /***********พ่อ แม่ พี่น้อง**********/
    
    public function userHasoneFather() 
    {
        return $this->hasOne(Father::class, 'user_id', 'id')->withDefault();
    }
    
    public function userHasoneMother() 
    {
        return $this->hasOne(Mother::class, 'user_id', 'id')->withDefault();
    }
    
    public function userHasoneSibling() 
    {
        return $this->hasOne(Sibling::class, 'user_id', 'id')->withDefault();
    }


    //--------------Relation to Resume---------------//

    public function userHasoneResumeToLocation()
    {
        return $this->userHasoneResume->hasOne(ResumeLocation::class, 'resume_id', 'id');
    }

    public function userHasoneResumeToJobPreference()
    {
        return $this->userHasoneResume->hasOne(ResumeJobPreferences::class, 'resume_id', 'id');
    }

    public function userHasmanyResumeToEducation()
    {
        return $this->userHasoneResume->hasMany(ResumeEducations::class, 'resume_id', 'id');
    }

    public function userHasmanyResumeToWorkExperiences()
    {
        return $this->userHasoneResume->hasMany(ResumeWorkExperiences::class, 'resume_id', 'id');
    }

    public function userHasmanyResumeToLangSkill()
    {
        return $this->userHasoneResume->hasMany(ResumeLangSkills::class, 'resume_id', 'id');
    }

    public function userHasmanyResumeToSkill()
    {
        return $this->userHasoneResume->hasMany(ResumeSkills::class, 'resume_id', 'id');
    }

    public function userHasmanyResumeToCertificate()
    {
        return $this->userHasoneResume->hasMany(ResumeCertificates::class, 'resume_id', 'id');
    }

    public function userHasmanyResumeToOtherContact()
    {
        return $this->userHasoneResume->hasMany(ResumeOtherContacts::class, 'resume_id', 'id');
    }

}
