<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;

use App\Models\DocEmp;

use App\Models\Idcard;
use App\Models\Bookbank;
use App\Models\Transcript;
use App\Models\Resume\Resume;
use App\Models\Resume\ResumeSkills;
use App\Models\Resume\ResumeLocation;
use App\Models\Resume\ResumeEducations;
use App\Models\Resume\ResumeLangSkills;
use Illuminate\Notifications\Notifiable;
use App\Models\Resume\ResumeCertificates;
use App\Models\Resume\ResumeOtherContacts;
use App\Models\Resume\ResumeJobPreferences;
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
        ];
    }

    /*เอาค่าของประสบการณ์มาเรียงเป็น HTML */

    public function getWorkExperienceSummaryAttribute(): string
    {
        // ใช้ map เพื่อจัดรูปแบบข้อมูลแต่ละรายการให้เป็น HTML String
        $summary = $this->userHasmanyWorkExperiences
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
                $data = $this->userHasOneResume;

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
                $data = $this->userHasOneIdcard;

                if (! $data || ! $data->date_of_birth) {
                    return 'กรุณาระบุวันเกิด'; // หรือ 0, หรือค่า default อื่นๆ ตามต้องการ
                }

                return 'อายุ '. Carbon::parse($data->date_of_birth)->age . ' ปี';
            }
        );
    }

    public function userHasoneResume()
    {
        return $this->hasOne(Resume::class, 'user_id', 'id');
    }
    
    public function userHasoneIdcard()
    {
        return $this->hasOne(Idcard::class, 'user_id', 'id');
    }
    
    public function userHasoneTranscript()
    {
        return $this->hasOne(Transcript::class, 'user_id', 'id');
    }
    
    public function userHasoneBookbank()
    {
        return $this->hasOne(Bookbank::class, 'user_id', 'id');
    }

    public function userHasmanyDocEmp()
    {
        return $this->hasMany(DocEmp::class, 'user_id', 'id');
    }

    //--------------Relation to Resume---------------//

    public function userHasOneResumeToLocation(): HasOne
    {
        return $this->userHasoneResume->hasOne(ResumeLocation::class, 'resume_id', 'id') ?? [];
    }

    public function userHasOneResumeToJobPreference(): HasOne
    {
        return $this->userHasoneResume->hasOne(ResumeJobPreferences::class, 'resume_id', 'id');
    }

    public function userHasManyResumeToEducation(): HasMany
    {
        return $this->userHasoneResume->hasMany(ResumeEducations::class, 'resume_id', 'id');
    }

    public function userHasManyResumeToWorkExperiences(): HasMany
    {
        return $this->userHasoneResume->hasMany(ResumeWorkExperiences::class, 'resume_id', 'id');
    }

    public function userHasManyResumeToLangSkill(): HasMany
    {
        return $this->userHasoneResume->hasMany(ResumeLangSkills::class, 'resume_id', 'id');
    }

    public function userHasManyResumeToSkill(): HasMany
    {
        return $this->userHasoneResume->hasMany(ResumeSkills::class, 'resume_id', 'id');
    }

    public function userHasManyResumeToCertificate(): HasMany
    {
        return $this->userHasoneResume->hasMany(ResumeCertificates::class, 'resume_id', 'id');
    }

    public function userHasManyResumeToOtherContact(): HasMany
    {
        return $this->userHasoneResume->hasMany(ResumeOtherContacts::class, 'resume_id', 'id');
    }
    
    
    /*บัตรประชาชน*/
    
}
