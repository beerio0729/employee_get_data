@php
use Carbon\Carbon;

$resume = $user->userHasoneResume;
$salary = $resume?->resumeHasoneJobPreference->expected_salary ?? null;
$availability_date = $resume?->resumeHasoneJobPreference->availability_date ?? null;

$resumeLocation = $resume?->resumeHasonelocation;
$resumeSameIdcard = $resumeLocation?->same_id_card ?? 0;
$resumeAddress = $resumeLocation?->address ?? null;
$resumeProvince = $resumeLocation?->resumeBelongtoprovince->name_th ?? null;
$resumeDistrict = $resumeLocation?->resumeBelongtodistrict->name_th ?? null;
$resumeSubdistrict = $resumeLocation?->resumeBelongtosubdistrict->name_th ?? null;
$resumeZipcode = $resumeLocation?->resumeBelongtosubdistrict->zipcode ?? null;

$idcard = $user->userHasoneIdcard;
$birth_day = date_format($idcard?->date_of_birth,"d /m /Y ") ?? null;
$age = Carbon::parse($idcard?->date_of_birth)->age;
$idcardAddress = $idcard?->address ?? null;
$idcardProvince = $idcard?->idcardBelongtoprovince->name_th ?? null;
$idcardDistrict = $idcard?->idcardBelongtodistrict->name_th ?? null;
$idcardSubdistrict = $idcard?->idcardBelongtosubdistrict->name_th ?? null;
$idcardZipcode = $idcard?->idcardBelongtosubdistrict->zipcode ?? null;

$marital = $user->userHasoneMarital;
$father = $user->userHasoneFather;
$mother = $user->userHasoneMother;
$sibling = $user->userHasoneSibling?->data;
$maleCount = collect($sibling)->where('gender', 'male')->count();
$femaleCount = collect($sibling)->where('gender', 'female')->count();

@endphp


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto Sans Thai" rel="stylesheet" data-navigate-track="">
    <title>{{$title}}</title>
    <style>
        /* GLOBAL STYLES */
        body {
            /**font-family: 'Noto Sans Thai'; */
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            /* รักษาสีพื้นหลังในการพิมพ์ */
        }

        .fa-file-pdf-o {
            font-size: 1.5em;
            vertical-align: middle;
            margin-right: 6px;
        }

        #button {
            float: inline-end;
            background-color: #ff1500ff;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 20px 0;
            cursor: pointer;
        }

        #button:hover {
            background-color: #a60000ff;
        }

        .page-container {
            width: 210mm;
            margin: 0 auto 100px;
            box-sizing: border-box;
            border: 1px solid transparent;
            /* ขอบใสสำหรับขอบเขต A4 */
        }

        /* HEADER & PHOTO POSITIONING */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
            margin-bottom: 5px;
            position: relative;
            /* สำหรับตำแหน่ง "อนุมัติจากบุคคล" */
        }

        /* ตำแหน่ง "อนุมัติจากบุคคล" ที่มุมขวาบน */
        .apply-status-box {
            position: absolute;
            top: -15px;
            /* ปรับให้สูงขึ้นเหนือหัวข้อหลัก */
            right: 0;
            border: 1px solid red;
            padding: 1px 4px;
            font-size: 8pt;
            color: red;
            background-color: transparent;
        }

        .header-left {
            width: 30%;
        }

        .header-center {
            width: 40%;
            text-align: center;
            padding-top: 30px;
            /* จัดตำแหน่งหัวข้อให้อยู่กลางตามรูป */
        }

        .main-title {
            font-size: 16pt;
            font-weight: bold;
        }

        .sub-title {
            font-size: 11pt;
            font-weight: bold;
        }

        .header-right {
            width: 30%;
            text-align: right;
        }

        .photo-box {
            border: 1px solid #5e5e5e;
            width: 106px;
            height: 132px;
            text-align: center;
            line-height: 100px;
            font-size: 12pt;
            float: right;
            margin-left: auto;
            /* เพิ่ม */
            overflow: hidden;
            /* ซ่อนส่วนเกินของรูป */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .photo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            /* ครอบรูปเต็ม div โดยไม่บิดสัดส่วน */
            display: block;
            /* กำจัด space ขอบล่างของ inline img */
        }

        /* SECTION HEADER (CAREER INTERESTS) */
        .section-header {
            background-color: #6ac4ffff;
            /* สีพื้นหลังเทาตามรูป */
            font-weight: bold;
            padding: 2px 5px;
            margin-top: 5px;
            border: 1px solid #5e5e5e;
            text-transform: uppercase;
            font-size: 10pt;
            text-align: center;
            /* จัดให้อยู่ตรงกลางตามรูป */
        }

        /* DATA TABLES (FORM FIELDS) */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        tr {
            height: 30px;
            border: 1px solid #5e5e5e;
        }

        td {
            white-space: normal;
        }

        .text-left {
            text-align: left;
        }

        .data-table td {
            padding: 2px 5px;

            vertical-align: middle;
            position: relative;
        }

        /* Column Widths (Adjusted for Career Interests) */
        .label-col {
            font-size: 10pt;
            font-weight: bold;
            overflow-wrap: break-word;
            border-right: 1px solid #5e5e5e;
            ;
            /*white-space: nowrap;*/
        }

        .input-col {
            font-size: 10pt;
            text-align: left;
        }

        .data-input-col {
            padding-bottom: 0;
        }

        /* INPUT LINE STYLING (The underline part) */
        .data-fill {
            border-bottom: 1px solid #5e5e5e;
            font-size: 10pt;
            font-weight: bold;
            color: #1a1a1a;
            border-right: 1px solid #5e5e5e;
            padding: 0 5px;
            word-wrap: break-word;
            line-height: 1.4;
            background-color: #b9e9ff;

        }

        .full-line {
            width: calc(100% - 4px);
        }

        .full-fill-line {
            width: 80%;
            /* เส้นยาวสำหรับ Salary */
            display: inline-block;
        }

        .date-fill {
            width: 10%;
            /* ช่องว่างสำหรับ Date/Month/Year */
            display: inline-block;
            text-align: center;
        }

        /* CHECKBOX STYLING */
        .checkbox-col {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .checkbox-group {
            display: flex;
            gap: 5px;
            align-items: center;
            /*flex-wrap: wrap;*/
            justify-content: space-between;
        }

        .checkbox-item {
            white-space: nowrap;
        }

        .data-checkbox {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1px solid #5e5e5e;
            vertical-align: middle;
            text-align: center;
            line-height: 8px;
        }

        /* MEDIA FOR PRINT/PDF */
        @media print {
            .page-container {
                padding: 0;
            }
        }



        /******************************************** */
        /* *** FLEXBOX STYLES (สำหรับ Personal Information) *** */

        /* Container หลักของส่วน Flexbox เพื่อกำหนดขอบเขต */
        .flex-container-wrapper {
            border-right: 1px solid #5e5e5e;
            ;
            border-left: 1px solid #5e5e5e;
            ;
        }

        /* DIV ที่ทำหน้าที่เป็น TR: จัดเรียง Cell ในแนวนอน */
        .flex-row-container {
            display: flex;
            align-items: stretch;
            flex-wrap: wrap;
            /* ให้เซลล์มีความสูงเท่ากัน */
            border-bottom: 1px solid #5e5e5e;
            ;
            min-height: 30px;
        }

        .noborder {
            border: none !important;
        }

        /* DIV ที่ทำหน้าที่เป็น TD: จัดองค์ประกอบภายในและกำหนดขอบขวา */
        .flex-cell {
            padding: 2px 0 2px 5px;
            /* ใช้ padding เดียวกับ data-table td */
            border-right: 1px solid #5e5e5e;
            ;
            flex-shrink: 0;
            font-size: 10pt;
            /* ใช้ font-size เดียวกับ label-col */
            display: flex;
            align-items: center;
            line-height: 1.2;
            min-width: 134px;
        }

        .cell2 {
            min-width: 0;
        }

        .wrap {
            line-height: 1.8;
        }

        /* Label: ใช้ความกว้างตามเนื้อหา (เหมือน label-col แต่ควบคุมด้วย Flex) */
        .flex-label-inner {
            font-weight: bold;
            white-space: nowrap;
            /* สำคัญ: ห้ามขึ้นบรรทัด เพื่อให้ความกว้างเป็นอิสระ */
            margin-right: 5px;
            flex-shrink: 0;
        }

        /* Input/ช่องกรอก: ยืดเต็มพื้นที่ที่เหลือใน Cell นั้นๆ */
        .flex-input-inner {
            flex-grow: 1;
            padding: 0 10px;
            min-height: 30px;
        }

        /* *** CSS สำหรับ Label Multi-Row Span *** */

        /* Container หลักของ Label + Rows ข้อมูล */
        .flex-container-multi-row {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            /* สำคัญ: ทำให้ flex-item ยืดเต็มความสูงของข้อมูลข้างๆ */
            border-bottom: 1px solid #5e5e5e;
            ;
            /* ขอบล่างของแถวหลัก */
        }

        /* ITEM 1: Container ของ Label (Registered Address) */
        .flex-item-container-multi-row:first-child {
            /* กำหนดความกว้างคงที่สำหรับ Label */
            flex-shrink: 0;
            border-right: 1px solid #5e5e5e;
            ;
            /* ขอบขวาของ Label ที่แบ่งกับข้อมูล */

            /* จัด Label ให้อยู่กึ่งกลางในแนวตั้ง */
            display: flex;
            /* justify-content: center;*/
        }

        /* ITEM 2: Container ของข้อมูลที่อยู่ (Rows 1 & 2) */
        .flex-item-container-multi-row:last-child {
            width: 100%;
            /* ใช้ความกว้างที่เหลือทั้งหมด */
        }

        /* Row ภายใน Address Container: ลบขอบล่างของแถวแรก (เพื่อให้ 2 แถวรวมกันเป็นกล่องเดียว) 
        .flex-item-container-multi-row:last-child>.flex-row-container:first-child {
            border-bottom: none !important;
        }*/

        /* Flex Cell สำหรับ Label: จัด Label ให้อยู่กลาง Cell */
        .flex-item-container-multi-row:first-child>.flex-cell {
            /* ใช้ flex-cell เดิมของคุณ แต่จัดให้อยู่กลางใน Label Container */
            display: flex;
            /* justify-content: center; */
            align-items: center;
        }


        /**********พิเศษสำหรับ skills***********/

        .con-data-text {

            border-left: 1px solid #5e5e5e;
            border-right: 1px solid #5e5e5e;
            border-bottom: 1px solid #5e5e5e;
            padding: 5px 10px;
            line-height: 2.2;
            font-weight: bold
        }

        .item-data-text {
            display: inline-block;
            margin-right: 15px;
        }

        /*.data-skill {
            border-right: 2px solid #00000054;
            padding: 2px 5px;
            word-wrap: break-word;
        }*/

        /*************ตารางทั่วไป**************/
        .normal-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 10pt;
            border: 1px solid #5e5e5e;
        }

        .normal-table th,
        .normal-table td {
            border: 1px solid #5e5e5e;
            text-align: center;
            /* กำหนดความสูงของเซลล์เพื่อให้ดูคล้ายกับในรูป */
        }

        .normal-table th {
            font-size: 1.05em;
        }

        .normal-table td {
            font-size: 10pt;
            font-weight: bold;
            color: #1a1a1a;
            padding: 0 5px;
            line-height: 1.4;
        }

        .sub-tr {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div id="main_pdf" class="page-container">
        <button id='button' onclick="downloadPdf()"><i class="fa fa-file-pdf-o"></i> Download as PDF</button>
        <div class="header-section">

            <div class="header-left">
            </div>
            <div class="header-center">
                <div class="main-title">ใบสมัครงาน</div>
                <div class="sub-title">APPLICATION FORM</div>
            </div>
            <div class="header-right">
                <div class="photo-box">
                    @if ($user->userHasmanyDocEmp()->where('file_name', 'image_profile')->first())
                    <img src="{{config('app.url')}}/storage/{{
                        $user->userHasmanyDocEmp()->where('file_name', 'image_profile')
                        ->first()->path
                    }}">
                    @else
                    Your Photo
                    @endif
                </div>
            </div>
        </div>

        <div class="section-header career-interests">
            <span>CAREER INTERESTS</span>
        </div>
        <table class="data-table">
            <tr>
                <td class="label-col">Position Applied</td>
                <td class="con-data-text">
                    @if($resume?->resumeHasoneJobPreference()->exists())
                    @foreach ($resume?->resumeHasoneJobPreference->position as $index => $item)
                    <span class="item-data-text">
                        {{$index+1}}.
                        <span class="data-fill">
                            {{$item}}
                        </span>
                    </span>
                    @endforeach
                    @else
                    <h4 style="text-align: center;">---------- No Data ----------</h4>
                    @endif
                </td>
            </tr>

            <tr>
                <td class="label-col">Location</td>
                <td class="con-data-text">
                    @if($resume?->resumeHasoneJobPreference()->exists())
                    @php
                    $province = \App\Models\Provinces::pluck('name_en', 'id')->toArray();
                    @endphp
                    @foreach ($resume?->resumeHasoneJobPreference->location as $index => $item)
                    <span class="item-data-text">
                        {{$index+1}}.
                        <span class="data-fill">
                            {{$province[$item]}}
                        </span>
                    </span>
                    @endforeach
                    @if(blank($resume?->resumeHasoneJobPreference->location))
                    <span style="text-align: center;">---------- no data ----------</span>
                    @endif
                    @endif

                </td>
            </tr>
            <tr>
                <td class="label-col">Expected Salary</td>
                <td class="con-data-text">
                    <span class=" data-fill">{{$salary}}</span> THB
                </td>
            </tr>
            <tr>
                <td class="label-col">Commencement Date</td>
                <td class="con-data-text">
                    <span class="data-fill">{{$availability_date}}</span>
                </td>
            </tr>
        </table>

        <!-----------PERSONAL INFORMATION----------->

        <div class="section-header">
            <span>PERSONAL INFORMATION</span>
        </div>

        <div class="flex-container-wrapper">

            <div class="flex-row-container"> <!-----------ชื่อไทย------------->
                <div class="flex-cell">
                    <span class="flex-label-inner">คำนำหน้า (นาย / นาง / นางสาว / อื่นๆ)</span>
                </div>
                <div class="flex-cell flex-input-inner">
                    <span class="data-fill">{{$idcard?->prefix_name_th}}</span>
                </div>
                <div class="flex-cell cell2">
                    <span class="flex-label-inner">ชื่อ - สกุล ไทย</span>
                </div>
                <div class="flex-cell flex-input-inner noborder">
                    <span class="data-fill">{{$idcard?->name_th}} {{$idcard?->last_name_th}}</span>
                </div>
            </div>

            <div class="flex-row-container"> <!-----------ชื่อ Eng------------->
                <div class="flex-cell">
                    <span class="flex-label-inner">Title (Mr. / Mrs. / Miss / Other)</span>
                </div>
                <div class="flex-cell flex-input-inner">
                    <span class="data-fill">{{$idcard?->prefix_name_en}}</span>
                </div>
                <div class="flex-cell cell2">
                    <span class="flex-label-inner">Name - Surname</span>
                </div>
                <div class="flex-cell flex-input-inner noborder">
                    <span class="data-fill">{{$idcard?->name_en}} {{$idcard?->last_name_en}}</span>
                </div>
            </div>

            <div class="flex-row-container"> <!------วันเกิด--อายุ--น้ำหนัก--ส่วนสูง----->
                <div class="flex-cell" style="flex-basis: 10%; flex-grow: 0;">
                    <span class="flex-label-inner">Date of Birth</span>
                </div>
                <div class="flex-cell flex-input-inner noborder">
                    <div style="display: flex; align-items: flex-end; gap: 5px;">
                        <span class="data-fill" style="display: inline-block;">{{$birth_day}}</span>
                        <span style="margin-left: 15px;" class="flex-label-inner">Age</span>
                        <span class="data-fill" style="display: inline-block;">{{$age}}</span> Years

                        <span style="margin-left: 15px;" class="flex-label-inner">Weight</span>
                        <span class="data-fill" style="display: inline-block;">{{$resume?->weight}}</span> Kg.

                        <span style="margin-left: 15px;" class="flex-label-inner">Height</span>
                        <span class="data-fill" style="display: inline-block;">{{$resume?->height}}</span> Cm.
                    </div>
                </div>
            </div>

            <div class="flex-row-container"> <!-----------สัญชาติ------------->
                <div class="flex-cell">
                    <span class="flex-label-inner">Nationality</span>
                </div>
                <div class="flex-cell flex-input-inner">
                    @if (!blank($idcard))
                    <span class="data-fill">Thai</span>
                    @endif

                </div>
                <div class="flex-cell cell2">
                    <span class="flex-label-inner">Religion</span>
                </div>
                <div class="flex-cell flex-input-inner noborder">
                    <span class="data-fill">{{$idcard?->religion}}</span>
                </div>
            </div>

            <div class="flex-row-container"> <!-----------เลขบัตรประชาชน------------->
                <div class="flex-cell">
                    <span class="flex-label-inner">Identification card No. / Passport No.</span>
                </div>
                <div class="flex-cell flex-input-inner noborder">
                    @php
                    function formatThaiId($id) {
                    return preg_replace("/(\d)(\d{4})(\d{5})(\d{2})(\d)/", "$1-$2-$3-$4-$5", $id);
                    }
                    @endphp
                    <span class="data-fill">{{formatThaiId($idcard?->id_card_number)}}</span>
                </div>
            </div>

            <div class="flex-container-multi-row"> <!---ที่อยู่ตามบัตรประชาชน--->
                <div class="flex-item-container-multi-row">
                    <div class="flex-cell noborder">
                        <span class="flex-label-inner">Registered Address</span>
                    </div>
                </div>
                <div class="flex-item-container-multi-row" style="width: 100%;">

                    <div class="flex-row-container" style="border-bottom: none;">
                        <div class="flex-cell flex-input-inner noborder">
                            <span class="data-fill">{{$idcardAddress}}</span>
                        </div>
                    </div>

                    <div class="flex-row-container" style="border: none">
                        <div class="flex-cell flex-input-inner noborder">
                            <div style="display: flex; align-items: flex-end; gap: 10px; width: 100%;">
                                <span>Sub District</span>
                                <span class="data-fill">{{$idcardSubdistrict}}</span>
                                <span>District</span>
                                <span class="data-fill">{{$idcardDistrict}}</span>
                                <span>Province</span>
                                <span class="data-fill">{{$idcardProvince}}</span>
                                <span>Postcode</span>
                                <span class="data-fill">{{$idcardZipcode}}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex-container-multi-row"> <!---ที่อยู่ตามบัตร Resume --->
                <div class="flex-item-container-multi-row">
                    <div class="flex-cell noborder">
                        <span class="flex-label-inner">Present Address</span>
                    </div>
                </div>
                <div class="flex-item-container-multi-row" style="width: 100%;">

                    <div class="flex-row-container"
                        @if($resumeSameIdcard)
                        style="border-bottom: none"
                        @endif>
                        <div class="flex-cell flex-input-inner noborder">
                            <div class="checkbox-group">
                                <span class="data-checkbox">
                                    @if($resumeSameIdcard)
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> My present address is the same as my registered address.
                            </div>
                        </div>
                    </div>
                    @if(!$resumeSameIdcard || blank($resumeSameIdcard ))
                    <div class="flex-row-container" style="border: none">
                        <div class="flex-cell flex-input-inner" style="border-right: none; flex-grow: 1;">
                            <span class="data-fill">{{$resumeAddress}}</span>
                        </div>
                    </div>

                    <div class="flex-row-container" style="border: none">
                        <div class="flex-cell flex-input-inner" style="border-right: none; flex-grow: 4;">
                            <div style="display: flex; align-items: flex-end; gap: 10px; width: 100%;">
                                <span>Sub District</span>
                                <span class="data-fill" style="display: inline-block;">{{$resumeSubdistrict}}</span>
                                <span>District</span>
                                <span class="data-fill" style="display: inline-block;">{{$resumeDistrict}}</span>
                                <span>Province</span>
                                <span class="data-fill" style="display: inline-block;">{{$resumeProvince}}</span>
                                <span>Postcode</span>
                                <span class="data-fill" style="display: inline-block;">{{$resumeZipcode}}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!--<div class="flex-row-container"> ----ที่อยู่ปัจจุบันอยู่บ้าน หอ หรือ บ้านเช่า----
                <div class="flex-cell">
                    <span class="flex-label-inner">Residence</span>
                </div>
                <div class="flex-cell flex-input-inner" style="border-right: none;">
                    <div class="checkbox-group">
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox"></span> Parent's House
                        </span>
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox"></span> Own House
                        </span>
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox"></span> Rented House
                        </span>
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox"></span> Others, please identify
                        </span>
                        <span class="data-fill">dcdcdcd</span>
                    </div>

                </div>
            </div>
            -->

            <div class="flex-row-container"> <!-----------ข้อมูลเบอร์ อีเมล------------>
                <div class="flex-cell">
                    <span class="flex-label-inner">Mobile No.</span>
                </div>
                <div class="flex-cell flex-input-inner">
                    <span class="data-fill">{{$resume?->tel}}</span>
                </div>
                <div class="flex-cell cell2">
                    <span class="flex-label-inner">Home Tel No.</span>
                </div>
                <div class="flex-cell flex-input-inner">
                    <span class="data-fill"></span>
                </div>
                <div class="flex-cell cell2">
                    <span class="flex-label-inner">Email</span>
                </div>
                <div class="flex-cell flex-input-inner" style="border-right: none;">
                    <span class="data-fill">{{$user->email}}</span>
                </div>
            </div>


            @if(!in_array(trim(strtolower($idcard?->prefix_name_en),"."), ['miss', 'mrs']))
            <div class="flex-row-container"> <!------สถานะการเกณฑ์หทหาร----->
                <div class="flex-cell" style="flex-basis: 10%; flex-grow: 0;">
                    <span class="flex-label-inner">Military Status</span>
                </div>
                <div class="flex-cell flex-input-inner" style="border-right: none;">
                    <div class="checkbox-group">
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox">
                                @if ($user->userHasoneMilitary?->type === 8)
                                <i class="fa fa-check"></i>
                                @endif
                            </span> Completed
                        </span>
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox">
                                @if (in_array($user->userHasoneMilitary?->result, ["ดำ","ผ่อนผัน", "ยกเว้น"]))
                                <i class="fa fa-check"></i>
                                @endif
                            </span> Exemted, because
                        </span>
                        @if (in_array($user->userHasoneMilitary?->result, ["ดำ","ผ่อนผัน"]))
                        <span class="data-fill">{{ config('iconf.marital')[$user->userHasoneMilitary?->result] ?? '' }}</span>
                        @else
                        <span class="data-fill">{{$user->userHasoneMilitary?->reason_for_exemption}}</span>
                        @endif
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox">
                                @if ($user->userHasoneMilitary?->result === 'แดง')
                                <i class="fa fa-check"></i>
                                @endif
                            </span> I will be drafted in year
                        </span>
                        @if ($user->userHasoneMilitary?->result === 'แดง')
                        <span class="data-fill">{{date_format($user->userHasoneMilitary?->date_to_army,"d /m /Y ")}}</span>
                        @endif
                    </div>

                </div>
            </div>
            @endif

            <div class="flex-container-multi-row noborder"> <!------สถานะแต่งงาน----->
                <div class="flex-item-container-multi-row">
                    <div class="flex-cell noborder">
                        <span class="flex-label-inner">Marital Status</span>
                    </div>
                </div>
                <div class="flex-item-container-multi-row">
                    <div class="flex-cell flex-input-inner" style="border-right: none; display: block; align-content: center;padding-right: 70px;">
                        <div class="checkbox-group">
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($marital?->status === 'single' || blank($marital))
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Single
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($marital?->status === 'married')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Married

                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($marital?->status === 'divorced')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Divorced
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($marital?->status === 'widowed')
                                    <i class="fa fa-check"></i>
                                    @endif</span> Widowed
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($marital?->status === 'separated')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Separated
                            </span>
                        </div>
                    </div>
                    @if ($marital?->status === 'married')
                    <div class="flex-row-container noborder"> <!-----------รายละเอียดคู่สมรส------------>
                        <div class="flex-cell cell2 noborder">
                            <span class="flex-label-inner">Name of Spouse : </span>
                            <span class="data-fill">นาง
                                @if ($idcard?->gender === 'male')
                                {{$marital?->woman}}
                                @else
                                {{$marital?->man}}
                                @endif
                            </span>
                        </div>
                        <div class="flex-cell cell2 noborder">
                            <span class="flex-label-inner cell2">Age : </span>
                            <span class="data-fill">{{$marital?->age}} Years</span>
                        </div>
                        <div class="checkbox-group" style="justify-content: center; gap: 5%; width: 25%">
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($marital?->alive)
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Alive
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($marital?->alive === 0)
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Pass Away
                            </span>
                        </div>
                    </div>
                    <div class="flex-row-container noborder"> <!-----------อาชีพคู่สมรส------------>
                        <div class="flex-cell cell2 noborder">
                            <span class="flex-label-inner">Occupation : </span>
                            <span class="data-fill">{{$marital?->occupation}}</span>

                        </div>
                        <div class="flex-cell cell2 noborder">
                            <span class="flex-label-inner cell2"> Company : </span>
                            <span class="data-fill">{{$marital?->company}} </span>

                        </div>
                        <div class="flex-cell cell2 noborder">
                            <span class="flex-label-inner cell2"> No . of Children : </span>
                            <span class="data-fill">{{$marital?->male + $marital?->female}} Person</span>

                        </div>
                        <div class="flex-cell cell2 noborder">
                            <span class="flex-label-inner cell2"> Male : </span>
                            <span class="data-fill">{{$marital?->male}} Person</span>

                        </div>
                        <div class="flex-cell cell2 noborder">
                            <span class="flex-label-inner cell2"> Female : </span>
                            <span class="data-fill">{{$marital?->female}} Person</span>
                        </div>



                    </div>
                    @endif
                </div>
            </div>

            <div class="flex-container-multi-row" style="@if(!blank($sibling))border-bottom: none;@endif border-top: 1px solid #5e5e5e;"> <!------พ่อแม่----->
                <div class="flex-item-container-multi-row">
                    <div class="flex-cell noborder">
                        <span class="flex-label-inner">Parent's Information</span>
                    </div>
                </div>
                <div class="flex-item-container-multi-row">
                    <div class="flex-row-container"> <!-----------พ่อ------------>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">Name of Father : </span>
                            <span class="data-fill">{{$father?->name}}</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2">Age : </span>
                            <span class="data-fill">{{$father?->age}} Years</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2">Nationality : </span>
                            <span class="data-fill">{{$father?->nationality}}</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">Occupation : </span>
                            <span class="data-fill">{{$father?->occupation}}</span>

                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2"> Company : </span>
                            <span class="data-fill">{{$father?->company}}</span>

                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2"> Tel : </span>
                            <span class="data-fill">{{$father?->tel}}</span>

                        </div>

                        <div class="checkbox-group" style="justify-content: center; gap: 5%; width: 25%">
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($father?->alive)
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Alive
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($father?->alive === 0)
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Pass Away
                            </span>
                        </div>


                    </div>
                    <div class="flex-row-container"> <!-----------แม่------------>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">Name of mother : </span>
                            <span class="data-fill">{{$mother?->name}}</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2">Age : </span>
                            <span class="data-fill">{{$mother?->age}} Years</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2">Nationality : </span>
                            <span class="data-fill">{{$mother?->nationality}}</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">Occupation : </span>
                            <span class="data-fill">{{$mother?->occupation}}</span>

                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2"> Company : </span>
                            <span class="data-fill">{{$mother?->company}}</span>

                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner cell2"> Tel : </span>
                            <span class="data-fill">{{$mother?->tel}}</span>

                        </div>

                        <div class="checkbox-group" style="justify-content: center; gap: 5%; width: 25%">
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($mother?->alive)
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Alive
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($mother?->alive === 0)
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Pass Away
                            </span>
                        </div>


                    </div>
                    <div class="flex-row-container noborder"> <!-----------พี่น้อง------------>
                        @php
                        $index = collect($sibling)->search(fn($item) => $item['you'] === true);
                        $order = $index !== false ? $index + 1 : 1;
                        @endphp
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">Sibling (including yourself) : </span>
                            <span class="data-fill">{{$maleCount + $femaleCount}} Person</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">Male : </span>
                            <span class="data-fill">{{$maleCount}} Person</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">Female : </span>
                            <span class="data-fill">{{$femaleCount}} Person</span>
                        </div>
                        <div class="flex-cell cell2 wrap noborder">
                            <span class="flex-label-inner">You are no. : </span>
                            <span class="data-fill">{{$order}}</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!----------ข้อมูลพี่น้อง เป็นตาราง---------->
        @if (!blank($sibling))
        <table class="normal-table">
            <thead>
                <tr>
                    <th rowspan="2">no</th>
                    <th rowspan="2">Name - Surname</th>
                    <th rowspan="2">Age</th>
                    <th rowspan="2">Occupation</th>
                    <th rowspan="2">Company/University</th>
                    <th rowspan="2">Position</th>
                </tr>
                <tr></tr>
            </thead>
            @foreach ($sibling as $index => $item)
            @if($item['you'])
            <tbody>
                <tr>
                    <td class="sub-tr">{{$index+1}}</td>
                    <td colspan="5" class="sub-tr">{{$item['name']}}</td>
                </tr>
            </tbody>
            @else
            <tbody>
                <tr>
                    <td class="sub-tr">{{$index+1}}</td>
                    <td class="sub-tr">{{$item['name'] ?? '-'}}</td>
                    <td class="sub-tr">{{$item['age'] ?? '-'}}</td>
                    <td class="sub-tr">{{$item['occupation'] ?? '-'}}</td>
                    <td class="sub-tr">{{$item['company'] ?? '-'}}</td>
                    <td class="sub-tr">{{$item['position'] ?? '-'}}</td>
                </tr>
            </tbody>
            @endif
            @endforeach
            @endif
        </table>


        <!---------------EDUCATION BACKGROUND--------------->

        <div class="section-header">
            <span>EDUCATION BACKGROUND</span>
        </div>
        <table class="normal-table">
            <thead>
                <tr>
                    <th rowspan="2">Education Level</th>
                    <th rowspan="2">University</th>
                    <th rowspan="2">Major Subject</th>
                    <th rowspan="2">Degree</th>
                    <th colspan="2">Year</th>
                    <th rowspan="2">GPA.</th>
                </tr>
                <tr>
                    <th>From</th>
                    <th>To</th>
                </tr>
            </thead>
            @if ($user->userHasmanyTranscript()->exists())
            @foreach ($user->userHasmanyTranscript as $item)
            <tbody>
                <tr>
                    <td>{{$item->education_level ?? "No Data"}}</td>
                    <td>{{$item->institution ?? "No Data"}}</td>
                    <td>{{$item->major ?? "No Data"}}</td>
                    <td>{{$item->degree ?? "No Data"}}</td>
                    <td>{{date('Y', strtotime($item->date_of_admission)) ?? "No Data"}}</td>
                    <td>{{date('Y', strtotime($item->date_of_graduation)) ?? "No Data"}}</td>
                    <td>{{$item->gpa}}</td>
                </tr>
            </tbody>
            @endforeach
            @else
            <tbody>
                <tr>
                    <td colspan="7">
                        <h4>---------- No Data ----------</h3>
                    </td>
                </tr>
            </tbody>
            @endif
        </table>

        <!----------------PROFESSIONAL EXPERIENCE----------------->

        <div class="section-header">
            <span>PROFESSIONAL EXPERIENCE</span>
        </div>
        <table class="normal-table">
            <thead>
                <tr>
                    <th colspan="2">Duration</th>
                    <th rowspan="2">Company</th>
                    <th rowspan="2">Position</th>
                    <th colspan="2">Salary</th>
                    <th rowspan="2">Reason for leaving</th>
                </tr>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Start</th>
                    <th>Latest</th>
                </tr>
            </thead>
            @if ($resume?->resumeHasmanyWorkExperiences()->exists())
            @foreach ($resume?->resumeHasmanyWorkExperiences as $item)
            <tbody>
                <tr>
                    <td class="sub-tr">{{$item->start}}</td>
                    <td class="sub-tr">{{$item->last}}</td>
                    <td>{{$item->company}}</td>
                    <td>{{$item->position}}</td>
                    <td class="sub-tr">{{$item->salary}}</td>
                    <td class="sub-tr">-</td>
                    <td>{{$item->reason_for_leaving}}</td>

                </tr>
            </tbody>
            @endforeach

            @else
            <tbody>
                <tr>
                    <td colspan="7">
                        <h4>---------- No Data ----------</h3>
                    </td>
                </tr>
            </tbody>
            @endif
        </table>

        <!---------------LANGUAGE SKILLS----------------->

        <div class="section-header">
            <span>LANGUAGE SKILLS</span>
        </div>
        <table class="normal-table">
            <thead>
                <tr>
                    <th rowspan="2">Language</th>
                    <th rowspan="2">Listening</th>
                    <th rowspan="2">Speaking</th>
                    <th rowspan="2">Writing</th>
                </tr>
                <tr></tr>
            </thead>
            @if ($resume?->resumeHasmanyLangSkill()->exists())
            @foreach ($resume?->resumeHasmanyLangSkill as $item)
            <tbody>
                <tr>
                    <td>{{ucwords($item->language)}}</td>
                    <td>
                        <div class="checkbox-group">
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->listening === 'fluent')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Fluent
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->listening === 'good')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Good
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->listening === 'fair')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Fair
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="checkbox-group">
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->speaking === 'fluent')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Fluent
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->speaking === 'good')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Good
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->speaking === 'fair')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Fair
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="checkbox-group">
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->writing === 'fluent')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Fluent
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->writing === 'good')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Good
                            </span>
                            <span class="input-col checkbox-item">
                                <span class="data-checkbox">
                                    @if ($item->writing === 'fair')
                                    <i class="fa fa-check"></i>
                                    @endif
                                </span> Fair
                            </span>
                        </div>
                    </td>
                </tr>
            </tbody>
            @endforeach
            @else
            <tbody>
                <tr>
                    <td colspan="4">
                        <h4>---------- No Data ----------</h3>
                    </td>
                </tr>
            </tbody>
            @endif
        </table>

        <!-----------Other Skill------------->

        <div class="section-header">
            <span>OTHER SKILLS</span>
        </div>

        <div class='con-data-text'> <!---------สกิลอื่นๆ------->
            @if($resume?->resumeHasmanySkill()->exists())
            @foreach ($resume?->resumeHasmanySkill as $index => $item)
            <span class="item-data-text">
                {{$index+1}}.
                <span class="data-fill">
                    {{$item['skill_name']}}
                </span>
            </span>
            @endforeach
            @else
            <h4 style="text-align: center;">---------- No Data ----------</h4>
            @endif
        </div>
        <table class="con-data-text"> <!-------มีใบขับขี่ไหม--------->
            <thead>
                <tr class="text-left" style="border: none;">
                    <th rowspan="2" style="width: 23%; padding-left: 10px;">Vehicles and License</th>
                    <th rowspan="1">
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox">
                                <i class="fa fa-check"></i>
                            </span> I have a car of my own
                        </span>
                    </th>

                    <th rowspan="1">
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox">
                                <i class="fa fa-check"></i>
                            </span> I have a car driving license
                        </span>
                    </th>
                </tr>
                <tr class="text-left" style="border: none;">
                    <th rowspan="1">
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox">
                                <i class="fa fa-check"></i>
                            </span> I have a motorcycle of my own
                        </span>
                    </th>
                    <th rowspan="1">
                        <span class="input-col checkbox-item">
                            <span class="data-checkbox">
                                <i class="fa fa-check"></i>
                            </span> I have a motorcycle driving license
                        </span>
                    </th>
                </tr>
            </thead>
        </table>



    </div>


    <!----------Java Script------------->
    <script>
        function downloadPdf() {

            document.getElementById('button').style.display = "none";

            window.print()

            window.location.reload();
        }
    </script>
</body>

</html>



















<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <title>{{ $title }}</title>
</head>

<body>
    <h1 id="content">{{ $user->userHasoneIdcard->idcard_number }}</h1>
    <button id='button' onclick="downloadPdf()">Download as PDF</button>
    <script>
        function downloadPdf() {
            const element = document.getElementById('content'); // Or a specific element, e.g., document.getElementById('content')
            const options = {
                margin: 10,
                filename: '{{ $user->userHasoneIdcard->name_th }}.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            //document.getElementById('button').remove();
            html2pdf().from(element).set(options).save();
        }
    </script>
</body>

</html> -->