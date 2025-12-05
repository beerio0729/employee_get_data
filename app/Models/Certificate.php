<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = "another_docs"; //ชื่อตาราง
    protected $fillable = [
        'doc_type',
        'data',
        'file_path',
        'date_of_issue', //วันที่ออกบัตร
        'date_of_expiry', //วันบัตรหมดอายุ
    ];
}
