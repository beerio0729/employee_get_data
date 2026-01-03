<?php

namespace App\Models\WorkStatus;

use Illuminate\Database\Eloquent\Model;

class PostEmploymentGrade extends Model
{
    protected $table = 'post_employment_grades';

    protected $fillable = [
        'name_th',  // อ้างอิง users
        'name_en', //
        'grade',
    ];
}
