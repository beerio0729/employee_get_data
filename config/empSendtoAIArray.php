<?php

use function PHPSTORM_META\type;

return [
    'resume' => [
        // Contact Info (Keys จาก Simplified Schema)
        'prefix_name' => [
            'type' => 'string',
            'description' => 'คำนำหน้าชื่อเช่น นาย, นางสาว, Mr. Miss ถ้าไม่มีก็พิจารณาจากรูปโปรไฟล์เอา ถ้าเป็นหญิงให้ใช้ น.ส. หรือ Miss ได้เลย'
        ],
        'name' => [
            'type' => 'string',
            'description' => 'ชื่อ (ภาษาไทยหรืออังกฤษ) ที่ระบุในเอกสาร'
        ],
        'last_name' => [
            'type' => 'string',
            'description' => 'นามสกุล (ภาษาไทยหรืออังกฤษ) ที่ระบุในเอกสาร'
        ],
        //'email' => ['type' => 'string', 'description' => 'อีเมลติดต่อ'],
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
                'start' => ['type' => 'string', 'description' => 'ช่วงเวลาเริ่มงาน เช่น YYYY/MM เท่านั้น'],
                'last' => ['type' => 'string', 'description' => 'ช่วงที่ลาออก เช่น YYYY/MM หรือ ปัจจุบัน'],
                'salary' => ['type' => 'string', 'description' => 'เงินเดือนล่าสุด (ตัวเลขเท่านั้น)'],
                'details' => ['type' => 'string', 'description' => 'รายละเอียดหน้าที่ความรับผิดชอบ'],
                'reason_for_leaving' => ['type' => 'string', 'description' => 'เหตุผลที่ลาออก'],
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
            'description' => 'รายการทักษะทางภาษามี',
            'items' => ['type' => 'object', 'properties' => [
                'language' => ['type' => 'string', 'description' => 'ให้เปลี่ยนเป็นภาษาอังกฤษ เช่น thai, japan เป็นต้น'],
                'speaking' => ['type' => 'string', 'description' => 'ระดับ: fluent, good, fair เท่านั้น'],
                'listening' => ['type' => 'string', 'description' => 'ระดับ: fluent, good, fair เท่านั้น'],
                'writing' => ['type' => 'string', 'description' => 'ระดับ: fluent, good, fair เท่านั้น'],
            ]]
        ],
        'skills' => [
            'type' => 'array',
            'description' => 'ทักษะความชำนาญด้านต่างๆ ไม่รวมเกี่ยวกับทักษะด้านการขับรถ',
            'items' => ['type' => 'object', 'properties' => [
                'skill_name' => ['type' => 'string', 'description' => 'ถ้าอยู่ใน List เดียวกันเช่น เป็น . หรือ - หรือเป็นข้อๆ ให้เอามาอยู่ใน cell เดียวกัน'],
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
        'position' => [
            'type' => 'array',
            'description' => 'ตำแหน่งงานที่ผู้สมัครต้องการ',
            'items' => ['type' => 'string', 'description' => 'ชื่อตำแหน่งงานที่ต้องการทำงาน']
        ],
        'location' => [
            'type' => 'array',
            'description' => 'พื้นที่ที่ต้องการไปทำงาน',
            'items' => ['type' => 'string', 'description' => 'จังหวัดที่ต้องการไปทำงาน (ถ้าเป็นกรุงเทพและปริมณฑลให้ถือว่าเป็นอันเดียวกัน สำหรับภาษาอังกฤษให้ใช้คำว่า "Bangkok Metropolitan Region" เท่านั้น)']
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

    'idcard' => [
        // Personal Info (Keys จาก Simplified Schema)
        'prefix_name_th' => ['type' => 'string'],
        'name_th' => ['type' => 'string'],
        'last_name_th' => ['type' => 'string'],
        'prefix_name_en' => ['type' => 'string'],
        'name_en' => ['type' => 'string'],
        'last_name_en' => ['type' => 'string'],
        'id_card_number' => ['type' => 'string', 'description' => 'รหัสบัตรประชาชน เก็บแค่ตัวเลขอย่างเดียว'],
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
            'description' => 'ระดับการศึกษา เช่น ปริญญาตรี, มัธยมศึกษา ถ้าเอกสารเป็นอังกฤษต้องใช้ภาษาอังกฤษ'
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
            'description' => 'วันที่/ปีที่เข้าศึกษา (รูปแบบ YYYY-MM-DD หรือ ปี ค.ศ.)'
        ],
        'date_of_graduation' => [
            'type' => 'string',
            'description' => 'วันที่/ปีที่สำเร็จการศึกษา/ได้รับวุฒิ (รูปแบบ YYYY-MM-DD หรือ ปี ค.ศ.)'
        ],
        'gpa' => [
            'type' => 'string', // ใช้ 'number' สำหรับตัวเลขทศนิยม
            'description' => 'เกรดเฉลี่ยสะสม (GPA/CGPA) เป็นตัวเลขทศนิยม 2 ตำแหน่ง'
        ],
        'check' => ['type' => 'string', 'description' => 'ตรวจสอบว่าเป็นเอกสารประเภท transcript จริงๆ ตอบกลับมาว่า yes หรือ no']
    ],

    'military' => [
        'id_card' => ['type' => 'string', 'description' => 'รหัสบัตรประชาชน เก็บแค่ตัวเลขอย่างเดียว'],
        'type' => ['type' => 'string', 'description' => 'ประเภทเอกสารเอาเช่น สด 9 เก็บเฉพาะตัวเลขเท่านั้น'],
        'result' => [
            'type' => 'string',
            'description' => "อ่านข้อความที่เขียนด้วยลายมือในข้อที่ ๒.๔ ผลการจับฉลาก เฉพาะใบ สด. 43. ค่าที่เก็บต้องเป็นคำในภาษาไทยเท่านั้น ตามเงื่อนไขดังนี้
                \n- ถ้ามีคำที่ใกล้เคียคำว่า ดำ หรือ ใบดำ ให้รีเทริน 'ดำ', 
                \n- ถ้ามีคำที่ใกล้เคียงคำว่า แดง, ทบ, ทร, ทอ, ให้รีเทริน 'แดง' 
                \n- ถ้าไม่มีข้อความเลย ให้รีเทริน 'ยกเว้น'
            "
        ],
        'reason_for_exemption' => [
            'type' => 'string',
            'description' => "อ่านข้อความที่เขียนด้วยลายมือในข้อที่ ๒.๑ **หากค่า result ที่ประมวลผลได้คือ 'ยกเว้น' เท่านั้น ให้เอาข้อความที่อ่านได้แปลเป้นภาษาอังกฤษ"
        ],
        'category' => [
            'type' => 'string',
            'description' => "ข้อมูลจำพวกบุคคลตามข้อ ๒.๓ บน สด. 43 (ผลการตรวจร่างกาย). ค่าที่เก็บต้องเป็นตัวเลข 1 ถึง 4 เท่านั้น (อาจปรากฏเป็นเลขไทย). **หากค่า result ที่ประมวลผลได้คือ 'ดำ' หรือ 'แดง' ให้ตีความ category เป็น '1' เสมอ หากลายมือไม่ชัดเจน** เพราะผู้ที่จับสลากจริงต้องเป็นจำพวกที่ ๑ เท่านั้น."
        ],
        'check' => ['type' => 'string', 'description' => 'ตรวจสอบว่าเป็นเอกสารประเภท ใบ สด. จริงๆ ตอบกลับมาว่า yes หรือ no'],
    ],

    'marital' => [
        'type' => ['type' => 'string', "description" => "ให้รีเทรินคำว่า 'married' หรือ 'divorced' ตามประเภทเอกสาร"],
        'registration_number' => ['type' => 'string', "description" => "เลขทะเบียนเอกสาร"],
        'man' => ['type' => 'string', "description" => "ชื่อฝ่ายชาย"],
        'woman' => ['type' => 'string', "description" => "ชื่อฝ่ายหญิง"],
        'issue_date' => ['type' => 'string', 'description' => 'วันออกเอกสาร ค.ศ. YYYY-MM-DD'],
    ],
];
