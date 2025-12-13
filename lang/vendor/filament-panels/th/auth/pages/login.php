<?php

return [

    'title' => 'เข้าสู่ระบบ',

    'heading' => 'เข้าสู่ระบบ',

    'actions' => [

        'register' => [
            'before' => 'หรือ',
            'label' => 'สมัครบัญชี',
        ],

        'request_password_reset' => [
            'label' => 'ลืมรหัสผ่านไหม',
        ],

    ],

    'form' => [
        
        'username' => [
            'label' => 'ชื่อผู้ใช้งาน',
            'placeholder' => 'สามารถใช้อีเมลหรือเบอร์โทรศัพท์ที่ลงทะเบียนไว้ได้'
        ],

        'email' => [
            'label' => 'ที่อยู่อีเมล',
        ],

        'password' => [
            'label' => 'รหัสผ่าน',
        ],

        'remember' => [
            'label' => 'จดจำฉัน',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'เข้าสู่ระบบ',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'คุณกรอกอีเมลหรือเบอร์โทรผิด! กรุณากรอกใหม่',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'จำนวนครั้งในการพยายามเข้าสู่ระบบได้ถึงขีดจำกัดแล้ว',
            'body' => 'กรุณาลองใหม่อีก :seconds วินาที',
        ],

    ],

];
