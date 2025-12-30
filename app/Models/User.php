<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Panel;
use App\Models\Document\DocEmp;
use App\Models\Document\Idcard;
use App\Models\Document\Marital;
use App\Models\Additional\Father;
use App\Models\Additional\Mother;
use App\Models\Document\Military;
use App\Models\Document\AnotherDoc;
use App\Models\Document\Transcript;
use App\Models\Document\Certificate;
use App\Models\WorkStatus\WorkStatus;
use App\Models\Document\Resume\Resume;
use Illuminate\Notifications\Notifiable;
use App\Models\Additional\AdditionalInfo;
use Filament\Models\Contracts\FilamentUser;
use App\Models\Additional\Sibling; //พี่น้อง
use App\Models\Document\Resume\ResumeSkills;
use App\Models\Document\Resume\ResumeLocation;
use App\Models\Document\Resume\ResumeEducations;
use App\Models\Document\Resume\ResumeLangSkills;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Document\Resume\ResumeCertificates;

use App\Models\Document\Resume\ResumeOtherContacts;
use App\Models\Document\Resume\ResumeJobPreferences;
use App\Models\Document\Resume\ResumeWorkExperiences;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;




class User extends Authenticatable implements FilamentUser
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
        'main_work_status',
        'email',
        'tel',
        'provider',
        'provider_id',
        'password',
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

    public function canAccessPanel(Panel $panel): bool
    {
        $role_name = $this->userBelongToRole->name;

        return match ($panel->getId()) {
            'admin' => in_array($role_name, ['admin', 'super_admin'], true),
            'employee' => in_array($role_name, ['admin', 'super_admin', 'employee'], true),
            default => false,
        };
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

    /********User Hasone work status********/

    public function userHasoneWorkStatus() //resume
    {
        return $this->hasOne(WorkStatus::class, 'user_id', 'id')->withDefault();
    }

    /**************User HasOne ไปที่เอกสาร*************/

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

    /***************** Helper**************/

    public function isPreEmployment()
    {
        return $this->userHasoneWorkStatus
            ?->WorkStatusBelongToWorkStatusDefDetail
            ?->workStatusDefDetailBelongsToWorkStatusDef
            ?->main_work_status === 'pre_employment';
    }
    
    public function isPostEmployment()
    {
        return $this->userHasoneWorkStatus
            ?->WorkStatusBelongToWorkStatusDefDetail
            ?->workStatusDefDetailBelongsToWorkStatusDef
            ?->main_work_status === 'post_employment';
    }
}
