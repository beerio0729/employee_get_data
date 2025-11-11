<?php

namespace App\Models\Resume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResumeCertificates extends Model
{

    use HasFactory;

    protected $table = "resume_certificates";
    protected $fillable = [
        "user_id",
        "name",
        "date_obtained",
    ];
}
