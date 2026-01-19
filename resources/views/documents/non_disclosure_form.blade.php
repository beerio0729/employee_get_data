@php
use Carbon\Carbon;
use App\Models\User;
use App\Models\Organization\OrganizationStructure;

$user = $data['user'];
$company = $data['company'];
$post_emp = $data['post_emp'];

$now_date = function() {$now = Carbon::now()->locale('th'); return $now->translatedFormat('j F ').$now->year + 543;};

$companyName = $company?->name ?? null;
$companyAddress = $company?->address ?? null;
$companyProvince = $company?->companyBelongtoProvince->name_th ?? null;
$companyDistrict = $company?->companyBelongtoDistrict->name_th ?? null;
$companySubdistrict = $company?->companyBelongtoSubdistrict->name_th ?? null;
$companySubdistrict_type = fn() => $company->province_id === 1 ? "แขวง" : "ต.";
$companyDistrict_type = fn() => $company->province_id === 1 ? "เขต" : "อ.";
$companyZipcode = $company?->companyBelongtosubdistrict->zipcode ?? null;
$companyAddressFull = "{$companyAddress} {$companySubdistrict_type()}{$companySubdistrict} {$companyDistrict_type()}{$companyDistrict} จ.{$companyProvince} {$companyZipcode}";

$idcard = $user->userHasoneIdcard;
$idcardName = "{$idcard->prefix_name_th} {$idcard->name_th} {$idcard->last_name_th}";
$idcardNum = $formattedIdCard = preg_replace('/^(\d)(\d{4})(\d{5})(\d{2})(\d)$/', '$1-$2-$3-$4-$5',$idcard->id_card_number);

$postEmp = fn() => OrganizationStructure::where('id' , $post_emp->lowest_org_structure_id)->first();

@endphp

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta text="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <title>{{$title}}</title>
    <style>
        /* GLOBAL STYLES */
        body {
            /**font-family: 'Noto Sans Thai'; */
            font-family: "Sarabun", sans-serif;
            font-size: 13pt;
            line-height: 35px;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            /* รักษาสีพื้นหลังในการพิมพ์ */
        }

        @media print {
            .page-break {
                page-break-after: always;
                /* หรือ break-after: page; */
            }
        }

        .page-container {
            width: 210mm;
            margin: 0 auto 100px;
            box-sizing: border-box;
            border: 1px solid transparent;
            /* ขอบใสสำหรับขอบเขต A4 */
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

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
            margin-bottom: 10px;
            position: relative;
            /* สำหรับตำแหน่ง "อนุมัติจากบุคคล" */
        }

        .header-left {
            width: 25%;
        }

        .header-center {
            width: 50%;
            text-align: center;
        }

        .header-right {
            width: 25%;
            text-align: right;
        }

        .main-title {
            font-size: 16pt;
            font-weight: 500;
        }

        .right {
            text-align: right;
        }

        .date_container {
            margin-top: 15px;
            margin-right: 215px;
        }

        .text {
            margin-top: 20px;
            text-indent: 50px;
        }
        
        i {
            text-decoration: underline;
        }

        .sub_text {
            text-indent: 50px;
        }

        .flex_sign_container {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
        }

        .sign {
            line-height: 34px;
            text-align: center;
            width: 50%;
        }
    </style>
</head>

<body>
    <div id="main_pdf" class="page-container">
        <button id='button' onclick="downloadPdf()"><i class="fa fa-file-pdf-o"></i> Download as PDF</button>
        <div class="header-section">
            <div class="header-left">
                <!-- Element ซ้าย -->
            </div>
            <div class="header-center">
                <div class="main-title">หนังสือสัญญาไม่เปิดเผยข้อมูลของบริษัท<br>(Non-Disclosure Agreement)</div>
            </div>
            <div class="header-right">
                <!-- Element ขวา -->
            </div>
        </div>
        <div class="header-section">
            <div class="header-left">
                <!-- Element ซ้าย -->
            </div>
            <div class="header-center">
                <!-- Element กลาง -->
            </div>
            <div class="header-right" style="width: 50%; text-align: left;">
                ทำที่ {{$company?->name}}<br>
                ที่อยู่ {{$companyAddressFull}}
            </div>
        </div>
        <div class="right date_container">
            วันที่ {{$now_date()}}
        </div>
        <div class="name-section">
            <div class="text">โดยหนังสือฉบับนี้ข้าพเจ้า</div>
            <div class="text">ชื่อ <i>{{$idcardName}}</i> บัตรประจำตัวประชาชนเลขที่ <i>{{$idcardNum}}</i></div>
            <div class="text">ทำงานกับบริษัทในตำแหน่ง <i>{{$postEmp()->name_th}}</i> แผนก/ฝ่าย <i>{{$postEmp()->parent->name_th}}</i> </div>
            <div class="text" style="text-indent: 0;">ขอให้คำมั่นเพื่อผูกพันตนต่อบริษัท ดังต่อไปนี้</div>
        </div>
        <div class="text_section">
            <div class="text">ข้อ 1. <B>“ข้อมูลลับ”</B> หมายถึงข้อมูลใดๆ ไม่ว่าจะอยู่ในลักษณะ และรูปแบบใด รวมถึงเทคโนโลยี แผนงาน ธุรกิจซอฟต์แวร์ การจัดการเครือข่าย ข้อเสนอ สัญญา วิธีการดำเนินงาน ความรู้ความชำนาญเฉพาะด้าน (Knowhow) ข้อกำหนดรายละเอียด (Specification) ข้อมูลดิบ กราฟ แผนภูมิ ตาราง รายละเอียดต่างๆ ของลูกค้า และอื่นๆ ที่คล้ายกัน ซึ่งข้าพเจ้าได้ทราบหรือได้รับ จากการเข้ามาทำงานตามสัญญาจ้างให้แก่บริษัท รวมทั้งบรรดาข้อมูลทั้งปวงที่ได้รับมาจากข้อมูลดังกล่าวซึ่งรวมถึงผลการประเมิน หรือข้อมูลที่เปิดเผยด้วยวาจาและ/หรือข้อมูลต่างๆ ที่ได้รับจากบุคคลที่สาม</div>
            <div class="text">ข้อ 2. ข้าพเจ้าจะเก็บรักษาข้อความและข้อมูลลับที่ได้จากการปฏิบัติงานตามสัญญาจ้าง และ/หรือที่ได้รับมาจากบุคคลที่สามเป็นความลับ โดยจะไม่เปิดเผยให้แก่บุคคลใดๆ และจะไม่กระทำการหรือร่วมกับบุคคลอื่นใดกระทำการคัดลอก เลียนแบบ สำเนา บันทึก แก้ไข ดัดแปลง ไม่ว่าโดยวิธีใด ๆ ตลอดระยะเวลาการปฏิบัติงานตามสัญญาจ้าง และแม้ภายหลังสิ้นสุดระยะเวลาตามสัญญาจ้างแล้วก็ตาม เว้นแต่ในกรณีดังต่อไปนี้</div>
            <div class="sub_text">2.1 เป็นการปฏิบัติตามกฎหมายที่ระบุในเรื่องนั้นโดยเฉพาะ หรือปฏิบัติตามคำสั่งศาล</div>
            <div class="sub_text">2.2 เป็นการเปิดเผยให้เฉพาะเป็นการภายในของบริษัทที่เกี่ยวข้องกับการดำเนินงานที่เกี่ยวข้องเท่านั้น</div>
            <div class="sub_text">2.3 ได้รับความยินยอมเป็นลายลักษณ์อักษรจากผู้มีอำนาจในการเปิดเผยข้อมูลของบริษัท</div>
            <div class="sub_text">2.4 เป็นข้อมูลที่รู้กันโดยทั่วไปอยู่แล้ว</div>
            <div class="sub_text">2.5 เป็นข้อมูลที่ข้าพเจ้าทราบอยู่ก่อนแล้วจากแหล่งข้อมูลอื่นๆ โดยเปิดเผยและมีหลักฐานที่ระบุชัด</div>
            <div class="text">ข้อ 3. หากข้าพเจ้าได้ฝ่าฝืนสัญญาตามข้อ 2. หรือบุคคลอื่นใดซึ่งได้ทราบข้อมูลของบริษัทจากข้าพเจ้าโดยมิชอบได้ฝ่าฝืนสัญญาตามข้อ 2. ข้าพเจ้ายินยอมชดใช้ค่าเสียหายทั้งปวงที่เกิดขึ้นจากเหตุดังกล่าวให้แก่บริษัทโดยไม่มีข้อโต้แย้งใด ๆ ทั้งสิ้น</div>
        </div>

        <div class="page-break"></div>

        <div class="signature-section">
            <div class="text">สัญญานี้ทำขึ้นสองฉบับ มีข้อความที่ตรงกัน ข้าพเจ้าได้อ่านและเข้าใจข้อความข้างต้นแล้ว จึงลงลายมือชื่อและประทับตรา (ถ้ามี) ไว้เป็นหลักฐานต่อหน้าพยาน พร้อมเก็บไว้ฝ่ายละหนึ่งฉบับ</div>
        </div>

        <!------------------- ลายเซ็นต์ --------------------->
        <div class="flex_sign_container">
            <div class="sign">
                ลงชื่อ.......................................................<br>
                (..............................................................)<br>
                กรรมการผู้จัดการ
            </div>
            <div class="sign">
                ลงชื่อ.......................................................<br>
                ( {{$idcardName}} )<br>
                พนักงาน
            </div>
        </div>

        <div class="flex_sign_container">
            <div class="sign">
                ลงชื่อ.......................................................<br>
                (..............................................................)<br>
                กรรมการผู้จัดการ
            </div>
            <div class="sign">
            </div>
        </div>
    </div>
    </div>
</body>


<script>
    function downloadPdf() {
        document.getElementById('button').style.display = "none";
        window.print();
        location.replace('/pdf/non_disclosure_form');
    }
</script>