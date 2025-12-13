<?php

return [

    'label' => 'โปรไฟล์',

    'form' => [
        
        'tel' => [
            'label' => 'เบอร์โทรศัพท์',
            'placeholder' => 'กรอกเบอร์โทรที่ท่านใช้อยู่',
            'afterlabel' => 'กรอกเฉพาะตัวเลขเท่านั้น',
        ],

        'email' => [
            'label' => 'ที่อยู่อีเมล',
        ],

        'name' => [
            'label' => 'ชื่อ-นามสกุล',
            'afterlabel' => 'กรุณาระบุชื่อ-นามสกลุจริง', 
        ],

        'password' => [
            'label' => 'รหัสผ่านใหม่',
        ],

        'password_confirmation' => [
            'label' => 'ยืนยันรหัสผ่านใหม่',
        ],

        'actions' => [

            'save' => [
                'label' => 'บันทึก',
            ],

        ],

    ],

    'notifications' => [

        'saved' => [
            'title' => 'บันทึกข้อมูลเรียบร้อย',
        ],

    ],

    'actions' => [

        'cancel' => [
            'label' => 'ยกเลิก',
        ],

    ],

];
