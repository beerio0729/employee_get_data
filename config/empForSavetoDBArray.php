<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Documents Fields (ใช้สำหรับ loop สร้าง $hasOneData/$hasManyData)
    |--------------------------------------------------------------------------
    */

    'idcard' => [
        'prefix_name_th',
        'name_th',
        'last_name_th',
        'prefix_name_en',
        'name_en',
        'last_name_en',
        'id_card_number',
        'religion', //ศาสนา
        'date_of_birth',
        'address',
        'province',
        'district',
        'subdistrict',
        'zipcode',
        'date_of_issue', //วันที่ออกบัตร
        'date_of_expiry', //วันบัตรหมดอายุ
        'check',
    ],

    'resume' => [
        // HasOne fields
        'full_name',
        'email',
        'tel',
        'weight',
        'height',
        'address',
        'subdistrict',
        'district',
        'province',
        'availability_date',
        'expected_salary',

        // HasMany fields
        'work_experience',
        'education',
        'lang_skill',
        'skills',
        'other_contacts',
        'desired_positions',
        'certificates',
        'check',
    ],

    'military' => [
        'military',
        'military_doc_detail',
        'check',
    ],

    'maritalDoc' => [
        'marital_doc_status',
        'spouse_name',
        'divorce_date',
        'check',
    ],

    'transcript' => [
        'cert_name',
        'cert_institution',
        'cert_year',
        'check',
    ],

];
