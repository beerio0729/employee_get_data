<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeOtherContacts extends Model
{
    use HasFactory;

    protected $table = "resume_other_contacts";
    protected $fillable = [
        'resume_id',
        'name',
        'email',
        'tel',
    ];
}
