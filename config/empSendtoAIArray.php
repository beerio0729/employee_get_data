<?php
return [
    'idcard' => [
        // Personal Info (Keys จาก Simplified Schema)
        'prefix_name_th' => ['type' => 'string'],
        'name_th' => ['type' => 'string'],
        'last_name_th' => ['type' => 'string'],
        'prefix_name_en' => ['type' => 'string'],
        'name_en' => ['type' => 'string'],
        'last_name_en' => ['type' => 'string'],
        'id_card_number' => ['type' => 'string', 'description' => 'รหัสบัตรประชาชน'],
        'religion' => ['type' => 'string'],
        'date_of_birth' => ['type' => 'string', 'description' => 'วันเกิดในรูปแบบ ค.ศ. YYYY-MM-DD'],
        'address' => ['type' => 'string', 'description' => 'ที่อยู่ตามบัตรประชาชน (ไม่รวม จังหวัด อำเภอ ตำบล รหัสไปรษณีย์)'],
        'subdistrict' => ['type' => 'string', 'description' => 'ชื่อตำบล'],
        'district' => ['type' => 'string', 'description' => 'ชื่ออำเภอ'],
        'province' => ['type' => 'string', 'description' => 'ชื่อจังหวัด'],
        'date_of_issue' => ['type' => 'string', 'description' => 'วันออกบัตร ค.ศ. YYYY-MM-DD'],
        'date_of_expiry' => ['type' => 'string', 'description' => 'วันบัตรหมดอายุ ค.ศ. YYYY-MM-DD'],
        'check' => ['type' => 'string', 'description' => 'ตรวจสอบว่าเป็นเอกสารประเภท บัตรประชาชน จริงๆ ตอบกลับมาว่า yes หรือ no'],
    ],


    // B. Sub-Schema สำหรับ Resume (Work/Skill/Contact Info)
    'resume' => [
        // Contact Info (Keys จาก Simplified Schema)
        'full_name' => ['type' => 'string', 'description' => 'ชื่อ-นามสกุล'],
        'email' => ['type' => 'string', 'description' => 'อีเมลติดต่อ'],
        'tel' => ['type' => 'string', 'description' => 'เบอร์โทรศัพท์มือถือ'],
        'weight' => ['type' => 'string', 'description' => 'น้ำหนัก (ตัวเลขเท่านั้น)'],
        'height' => ['type' => 'string', 'description' => 'ส่วนสูง (ตัวเลขเท่านั้น)'],

        // Position/Salary (Keys จาก Simplified Schema)
        'availability_date' => ['type' => 'string', 'description' => 'วันที่สะดวกเริ่มทำงาน (เช่น YYYY-MM-DD หรือ "ทันที")'],
        'expected_salary' => ['type' => 'string', 'description' => 'เงินเดือนที่คาดหวัง (ตัวเลขเท่านั้น)'],
        'address' => ['type' => 'string', 'description' => 'ที่อยู่ระบุ (ไม่รวม จังหวัด อำเภอ ตำบล รหัสไปรษณีย์)'],
        'subdistrict' => ['type' => 'string', 'description' => 'ชื่อตำบล'],
        'district' => ['type' => 'string', 'description' => 'ชื่ออำเภอ'],
        'province' => ['type' => 'string', 'description' => 'ชื่อจังหวัด'],
        'check' => ['type' => 'string', 'description' => 'ตรวจสอบว่าเป็นเอกสารประเภท resume จริงๆ ตอบกลับมาว่า yes หรือ no'],

        // Array 7 ชุด (Keys จาก Simplified Schema)
        'work_experience' => [
            'type' => 'array',
            'description' => 'รายการประสบการณ์ทำงาน',
            'items' => ['type' => 'object', 'properties' => [
                'company' => ['type' => 'string'],
                'position' => ['type' => 'string'],
                'duration' => ['type' => 'string', 'description' => 'ช่วงเวลาทำงาน เช่น "2015-07 - 2024-10"'],
                'salary' => ['type' => 'string', 'description' => 'เงินเดือนล่าสุด (ตัวเลขเท่านั้น)'],
                'details' => ['type' => 'string', 'description' => 'รายละเอียดหน้าที่ความรับผิดชอบ'],
            ]]
        ],
        'education' => [
            'type' => 'array',
            'description' => 'รายการประวัติการศึกษา',
            'items' => ['type' => 'object', 'properties' => [
                'institution' => ['type' => 'string'],
                'degree' => ['type' => 'string'],
                'major' => ['type' => 'string'],
                'gpa' => ['type' => 'string', 'description' => 'ดึงข้อมูลในรูปแบบเลขทศนิยม 2 ตำแหน่งเท่านั้น'],
                'education_level' => ['type' => 'string'],
                'faculty' => ['type' => 'string'],
                'last_year' => ['type' => 'string', 'description' => 'ปีที่สำเร็จการศึกษา (YYYY) ถ้าข้อมูลดิบเป็น พ.ศ. ให้แปลงเป็น ค.ศ.'],
            ]]
        ],
        'lang_skill' => [
            'type' => 'array',
            'description' => 'รายการทักษะทางภาษา',
            'items' => ['type' => 'object', 'properties' => [
                'language' => ['type' => 'string'],
                'speaking' => ['type' => 'string', 'description' => 'ระดับ: ดีเยี่ยม, พอใช้, อื่นๆ'],
                'reading' => ['type' => 'string'],
                'writing' => ['type' => 'string'],
            ]]
        ],
        'skills' => [
            'type' => 'array',
            'description' => 'รายการทักษะทางเทคนิค/เครื่องมือ',
            'items' => ['type' => 'object', 'properties' => [
                'skill_name' => ['type' => 'string'],
                'level' => ['type' => 'string', 'description' => 'ระดับความชำนาญ: สูง, กลาง, พื้นฐาน'],
            ]]
        ],
        'other_contacts' => [
            'type' => 'array',
            'description' => 'รายการบุคคลอ้างอิง/คนในครอบครัว',
            'items' => ['type' => 'object', 'properties' => [
                'name' => ['type' => 'string'],
                'tel' => ['type' => 'string', 'description' => 'เบอร์โทร'],
                'email' => ['type' => 'string', 'description' => 'อีเมล'],
            ]]
        ],
        'desired_positions' => [
            'type' => 'array',
            'description' => 'ตำแหน่งงานที่ผู้สมัครต้องการ',
            'items' => ['type' => 'string', 'description' => 'ชื่อตำแหน่งงานที่ต้องการ']
        ],
        'certificates' => [
            'type' => 'array',
            'description' => 'รายการเกียรติบัตรหรือใบประกาศที่ได้รับ',
            'items' => ['type' => 'object', 'properties' => [
                'name' => ['type' => 'string'],
                'date_obtained' => ['type' => 'string', 'description' => 'วันที่ได้รับ (ปี ค.ศ. YYYY หรือ YYYY-MM-DD)'],
            ]]
        ],
    ],

    // C. Sub-Schema สำหรับใบผ่านเกณฑ์ทหาร
    'military' => [
        'military' => ['type' => 'string', 'description' => 'สถานะการเกณฑ์ทหาร (พ้นภาระ, ยังไม่ถึงกำหนด, อื่นๆ)'], // ใช้ Key เดิม
        'military_doc_detail' => ['type' => 'string', 'description' => 'รายละเอียดเพิ่มเติมจากเอกสารเกณฑ์ทหาร'],
    ],

    // D. Sub-Schema สำหรับใบสมรส/หย่า (Field เพิ่มเติม)
    'maritalDoc' => [
        'marital_doc_status' => ['type' => 'string', 'description' => 'สถานะการสมรสที่ระบุในเอกสาร (สมรส, หย่า, หม้าย, อื่นๆ)'],
        'spouse_name' => ['type' => 'string', 'description' => 'ชื่อคู่สมรส (ถ้ามี)'],
        'divorce_date' => ['type' => 'string', 'description' => 'วันหย่า (ถ้ามี)'],
    ],

    // E. Sub-Schema สำหรับวุฒิการศึกษา (แยกจาก Array Education ใน Resume)
    'transcript' => [
        'prefix_name' => [
            'type' => 'string',
            'description' => 'คำนำหน้าชื่อนักศึกษาตามที่ระบุในเอกสาร เช่น นาย, นางสาว'
        ],
        'name' => [
            'type' => 'string',
            'description' => 'ชื่อนักศึกษา (ภาษาไทยหรืออังกฤษ) ที่ระบุในเอกสาร'
        ],
        'last_name' => [
            'type' => 'string',
            'description' => 'นามสกุลนักศึกษา (ภาษาไทยหรืออังกฤษ) ที่ระบุในเอกสาร'
        ],
        'institution' => [
            'type' => 'string',
            'description' => 'ชื่อเต็มของสถาบัน/มหาวิทยาลัยที่ออกวุฒิการศึกษา'
        ],
        'degree' => [
            'type' => 'string',
            'description' => 'ชื่อเต็มของวุฒิการศึกษา เช่น วิศวกรรมศาสตรบัณฑิต, บริหารธุรกิจบัณฑิต'
        ],
        'education_level' => [
            'type' => 'string',
            'description' => 'ระดับการศึกษา เช่น ปริญญาตรี, ปริญญาโท, ปวส.'
        ],
        'faculty' => [
            'type' => 'string',
            'description' => 'ชื่อเต็มของคณะวิชาที่สำเร็จการศึกษา'
        ],
        'major' => [
            'type' => 'string',
            'description' => 'ชื่อเต็มของสาขาวิชา/วิชาเอกที่สำเร็จการศึกษา'
        ],
        'minor' => [
            'type' => 'string',
            'description' => 'ชื่อเต็มของวิชาโทที่สำเร็จการศึกษา (ถ้ามี ถ้าไม่มีให้ null)'
        ],
        'date_of_admission' => [
            'type' => 'string',
            'description' => 'วันที่/ปีที่เข้าศึกษา (รูปแบบ YYYY-MM-DD หรือ ปี พ.ศ.)'
        ],
        'date_of_graduation' => [
            'type' => 'string',
            'description' => 'วันที่/ปีที่สำเร็จการศึกษา/ได้รับวุฒิ (รูปแบบ YYYY-MM-DD หรือ ปี พ.ศ.)'
        ],
        'gpa' => [
            'type' => 'string', // ใช้ 'number' สำหรับตัวเลขทศนิยม
            'description' => 'เกรดเฉลี่ยสะสม (GPA/CGPA) เป็นตัวเลขทศนิยม 2 ตำแหน่ง'
        ],
        'check' => ['type' => 'string', 'description' => 'ตรวจสอบว่าเป็นเอกสารประเภท transcript จริงๆ ตอบกลับมาว่า yes หรือ no'],
    ],
    
    'bookbank' => [
        'name' => ['type' => 'string', 'description' => 'ชื่อบัญชีถ้ามีภาษาไทยให้เอาภาษาไทย'], // ใช้ Key เดิม
        'bank_name' => ['type' => 'string', 'description' => 'ชื่อธนาคารถ้ามีภาษาไทยให้เอาภาษาไทย'],
        'bank_id' => ['type' => 'string', 'description' => 'เอาแต่ตัวเลขอย่างเดียว'],
        'check' => ['type' => 'string', 'description' => 'ตรวจสอบว่าเป็นเอกสารประเภท สมุดบัญชีธนาคาร จริงๆ ตอบกลับมาว่า yes หรือ no'],
    ],
];
