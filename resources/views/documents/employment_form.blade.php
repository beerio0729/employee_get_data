@php
use Carbon\Carbon;
use App\Models\Organization\OrganizationStructure;
$user = $data['user'];
$company = $data['company'];
$post_emp = $data['post_emp'];

$now_date = function ()
{
$now = Carbon::now()->locale('th');
return $now->translatedFormat('j F ').$now->year + 543;
};

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
$idcardNum = $formattedIdCard = preg_replace(
'/^(\d)(\d{4})(\d{5})(\d{2})(\d)$/',
'$1-$2-$3-$4-$5',$idcard->id_card_number);
$idcardAddress = $idcard?->address ?? null;
$idcardProvince = $idcard?->idcardBelongtoprovince->name_th ?? null;
$idcardDistrict = $idcard?->idcardBelongtodistrict->name_th ?? null;
$idcardSubdistrict = $idcard?->idcardBelongtosubdistrict->name_th ?? null;
$idcardSubdistrict_type = fn() => $idcard->province_id === 1 ? "แขวง" : "ต.";
$idcardDistrict_type = fn() => $idcard->province_id === 1 ? "เขต" : "อ.";
$idcardZipcode = $idcard?->idcardBelongtosubdistrict->zipcode ?? null;
$idcardAddressFull = "{$idcardAddress} {$idcardSubdistrict_type()}{$idcardSubdistrict} {$idcardDistrict_type()}{$idcardDistrict} จ.{$idcardProvince} {$idcardZipcode}";

$postEmp_position = fn() => OrganizationStructure::where('id' , $post_emp->lowest_org_structure_id)->first()->name_th;
$postEmp_hired_at = fn() => Carbon::parse($post_emp->hired_at)->locale('th')->translatedFormat('j F ').
Carbon::parse($post_emp->hired_at)->locale('th')->year + 543;

$salary = $post_emp->salary;
$salaryUse = number_format($salary, 2);
$salaryText = thaiBahtText($salary);



function thaiBahtText(float|int|string $amount): string
{
$formatter = new NumberFormatter('th_TH', NumberFormatter::SPELLOUT);
$formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, '%spellout-numbering');

$amount = number_format((float) $amount, 2, '.', '');
[$baht, $satang] = explode('.', $amount);

$bahtText = $formatter->format((int) $baht);

if ((int) $satang === 0) {
return $bahtText . 'บาทถ้วน';
}

$satangText = $formatter->format((int) $satang);

return $bahtText . 'บาท' . $satangText . 'สตางค์';
}

@endphp
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            margin-bottom: 30px;
            position: relative;
            /* สำหรับตำแหน่ง "อนุมัติจากบุคคล" */
        }

        .header-left {
            width: 30%;
        }

        .header-center {
            width: 40%;
            text-align: center;
        }

        .header-right {
            width: 30%;
            text-align: right;
        }

        .main-title {
            font-size: 19pt;
            font-weight: 700;
        }

        .sub-title {
            font-size: 11pt;
            font-weight: bold;
        }

        .text-indent {
            line-height: 34px;
            text-indent: 40pt;
            text-align: left;
            text-align: justify;
        }

        i {
            text-decoration: underline;
        }

        .flex_detail_container {
            display: flex;
        }

        .main {
            margin: 20px 0;
            font-weight: bold;
        }

        .num {
            width: 50px;
            line-height: 34px;
        }

        .detail {
            line-height: 34px;
            width: 95%;
            text-align: justify;
        }

        .l3 {
            padding-left: 12px;
        }

        .day_work {
            width: 220px;
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
                <div class="main-title">สัญญาจ้างแรงงาน</div>
            </div>
            <div class="header-right">
                <!-- Element ขวา -->
            </div>
        </div>
        <div class="intro-section">
            <div class="text-indent">
                สัญญาจ้างแรงงานนี้ (<B>“สัญญา”</B>) จัดทำขึ้นเมื่อวันที่ <i>{{$now_date()}}</i> ระหว่าง <i>{{$companyName}}</i> ซึ่งจัดตั้งขึ้น
                ตามกฎหมายไทยสำนักงานใหญ่ตั้งอยู่เลขที่ <i>{{$companyAddressFull}}</i> (<B>“บริษัท”</B>) ฝ่ายหนึ่ง
            </div>
            <div class="text-indent">
                กับ <i>{{$idcardName}}</i> บัตรประชาชน/หนังสือเดินทางเลขที่ <i>{{$idcardNum}}</i> อยู่บ้านเลขที่ <i>{{$idcardAddressFull}}</i> (<B>“พนักงาน”</B>) อีกฝ่ายหนึ่ง
            </div>
            <div class="text-indent">
                บริษัทประสงค์ว่าจ้างพนักงานในตำแหน่ง <i>{{$postEmp_position()}}</i> ซึ่งจะต้องรายงานต่อผู้บังคับบัญชาตำแหน่ง
                ....................................................... รายละเอียดเป็นไปตามข้อตกลงและเงื่อนไขที่ระบุในสัญญานี้
                พนักงานประสงค์ที่จะได้รับการจ้างจากบริษัท รายละเอียดเป็นไปตามข้อตกลงและเงื่อนไขที่ระบุในสัญญานี้
            </div>
        </div>
        <div class="detail-section">
            <!------------------- ส่วนที่ 1 --------------------->
            <div class="flex_detail_container main">
                <div class="num">1.</div>
                <div class="detail">ตำแหน่งงาน</div>
            </div>
            <div class="flex_detail_container">
                <div class="num">1.1</div>
                <div class="detail"><U>การจ้างงานพนักงาน</U> บริษัทตกลงว่าจ้างพนักงานและพนักงานยอมรับการจ้างงานกับบริษัท เริ่มตั้งแต่วันที่ <i>{{$postEmp_hired_at()}}</i> (“วันที่มีผลบังคับใช้”) รายละเอียดเป็นไปตามข้อตกลงและเงื่อนไข</div>
            </div>
            <div class="flex_detail_container">
                <div class="num">1.2</div>
                <div class="detail"><U>ระยะเวลาทดลองงาน</U> พนักงานจะต้องผ่านระยะเวลาทดลองงาน 119 (หนึ่งร้อยสิบเก้า) วัน เริ่มตั้งแต่วันที่สัญญานี้มีผลบังคับใช้ และในระหว่างระยะเวลาทดลองงานนี้ บริษัทอาจบอกเลิกสัญญาได้โดยแจ้งพนักงานล่วงหน้าเป็นลายลักษณ์อักษรไม่น้อยกว่า 1 (หนึ่ง) งวดเงินเดือน ซึ่งถือเป็นดุลพินิจฝ่ายเดียวของบริษัทที่จะพิจารณาว่า ตำแหน่งหน้าที่ของพนักงานที่บริษัทแจ้งหรือประกาศไปไม่เหมาะกับพนักงาน หรือพนักงานไม่ปฏิบัติตามสัญญาหรือข้อบังคับการทำงานหรือจรรยาบรรณในการดำเนินธุรกิจของบริษัท ในส่วนพนักงานผู้ประสงค์บอกเลิกสัญญานี้ จะต้องแจ้งบริษัทล่วงหน้าเป็นลายลักษณ์อักษรไม่น้อยกว่า 1 (งวด) เงินเดือน เช่นเดียวกัน</div>
            </div>

            <!------------------- ส่วนที่ 2 --------------------->
            <div class="flex_detail_container main">
                <div class="num">2.</div>
                <div class="detail">ตำแหน่งงาน</div>
            </div>
            <div class="flex_detail_container">
                <div class="num">2.1</div>
                <div class="detail">
                    พนักงานจะปฏิบัติงานในตำแหน่ง <i>{{$postEmp_position()}}</i> ซึ่งจะต้องรายงานต่อผู้บังคับบัญชา ตำแหน่ง .................................................... และจะต้องปฏิบัติงานและรับผิดชอบหน้าที่ของพนักงานตามขอบเขตการทำงานตามที่ระบุในเอกสารแนบท้าย ตลอดระยะเวลาสัญญานี้
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">2.2</div>
                <div class="detail">
                    พนักงานจะต้องปฏิบัติงานและรับผิดชอบหน้าที่ตามขอบเขตการทำงานเพื่อให้เกิดความพึงพอใจกับบริษัทด้วยความสุจริต ตลอดจนพนักงานจะต้องมีความละเอียดรอบคอบในการทำงาน เอาใจใส่ ขยัน และมีทักษะในการทำงานที่ดี ในทุกสถานการณ์
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">2.3</div>
                <div class="detail">
                    พนักงานจะต้องปฏิบัติตามข้อบังคับการทำงานหรือจรรยาบรรณของบริษัท รวมถึงนโยบายหรือคำสั่งโดยชอบด้วยกฎหมายของบริษัท
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">2.4</div>
                <div class="detail">
                    ตลอดระยะเวลาการจ้างงาน ถ้ามีเหตุจำเป็นหรือผู้ว่าจ้างเห็นว่าเหมาะสม ผู้ว่าจ้างมีสิทธิ์ที่จะเปลี่ยนแปลง โอนย้ายหน้าที่การงาน ตำแหน่งงาน สถานที่ทำงานและวันเวลาทำงานปกติของพนักงานได้ โดยที่ผู้รับจ้างต้องปฏิบัติตามโดยไม่มีเงื่อนไขใดๆ ทั้งสิ้น
                </div>
            </div>

            <div class="page-break"></div>
            <!------------------- ส่วนที่ 3 --------------------->
            <div class="flex_detail_container main">
                <div class="num">3.</div>
                <div class="detail">ค่าจ้างและสวัสดิการของพนักงาน</div>
            </div>
            <div class="flex_detail_container">
                <div class="num"></div>
                <div class="detail">
                    พนักงานให้ความยินยอมล่วงหน้าต่อบริษัทในการหักเงินภาษีที่เกี่ยวข้องทั้งหมด
                    รวมถึงภาษีเงินเดือน โบนัส และสวัสดิการ
                    พนักงานจะได้รับค่าจ้างและสวัสดิการ ดังนี้
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">3.1</div>
                <div class="detail">
                    ค่าจ้างรายเดือน พนักงานจะได้รับค่าจ้างรายเดือนรวม
                    เป็นจำนวนเงิน <i>{{$salaryUse}}</i> บาท (<i>{{$salaryText}}</i>)
                    บริษัทมีสิทธิหักภาษี ณ ที่จ่าย และหักเงินใดๆ จากค่าจ้างรายเดือน
                    และสวัสดิการของพนักงานเพื่อชำระให้แก่เจ้าหน้าที่ของรัฐ
                    หรือกองทุนประกันสังคม และ/หรือกองทุนอื่นๆ
                    ตามที่กฎหมายกำหนด
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">3.2</div>
                <div class="detail">
                    <U>สวัสดิการของพนักงาน</U> พนักงานจะได้รับสวัสดิการ ดังต่อไปนี้ ซึ่งบริษัทสงวนสิทธิในการปรับเปลี่ยนสวัสดิการเหล่านี้เป็นคราวๆ ไป
                    <div class="flex_detail_container">
                        <div class="num">3.2.1</div>
                        <div class="detail l3">
                            สวัสดิการพื้นฐานของพนักงาน
                            พนักงานจะได้รับสวัสดิการพื้นฐานจากบริษัทในฐานะที่เป็นพนักงานเต็มเวลาของบริษัทตามที่ระบุในข้อบังคับการทำงานบริษัท หรือประกาศของบริษัทที่เกี่ยวข้อง
                        </div>
                    </div>
                </div>
            </div>

            <!------------------- ส่วนที่ 4 --------------------->
            <div class="flex_detail_container main">
                <div class="num">4.</div>
                <div class="detail">วันทำงานปกติ ชั่วโมงทำงานปกติ ช่วงเวลาพัก และวันหยุดประจำสัปดาห์</div>
            </div>

            <div class="flex_detail_container">
                <div class="num"></div>
                <div class="detail">
                    พนักงานจะต้องปฏิบัติหน้าที่ของตนเองตามช่วงเวลาทางการของการทำงาน
                    <div class="flex_detail_container">
                        <div class="day_work">วันทำงานปกติ :</div>
                        <div class="detail">
                            พนักงานจะต้องปฏิบัติหน้าที่ของตนเองตามช่วงเวลาทางการของการทำงาน
                        </div>
                    </div>
                    <div class="flex_detail_container">
                        <div class="day_work">ชั่วโมงทำงานปกติ :</div>
                        <div class="detail">
                            ตั้งแต่เวลา 08.30 – 17.30 น.<br>
                            (ชั่วโมงทำงานปกติสามารถยืดหยุ่นเปลี่ยนแปลงได้ตามนโยบายของบริษัท)
                        </div>
                    </div>
                    <div class="flex_detail_container">
                        <div class="day_work">เวลาพัก :</div>
                        <div class="detail">
                            12.00-13.00 น. (สามารถยืดหยุ่นเปลี่ยนแปลงได้ตามนโยบายของบริษัท)
                        </div>
                    </div>
                    <div class="flex_detail_container">
                        <div class="day_work">วันหยุดประจำสัปดาห์ :</div>
                        <div class="detail">
                            วันเสาร์ถึงวันอาทิตย์<br>
                            (วันหยุดประจำสัปดาห์อาจจะเป็นวันทำงานปกติตามแต่ดุลพินิจของบริษัท)
                        </div>
                    </div>
                </div>
            </div>

            <!------------------- ส่วนที่ 5 --------------------->
            <div class="flex_detail_container main">
                <div class="num">5.</div>
                <div class="detail">ลา วันหยุดตามเพณี และวันหยุดพักผ่อนประจำปี</div>
            </div>
            <div class="flex_detail_container">
                <div class="num">5.1</div>
                <div class="detail">
                    <U>ลาป่วย</U> พนักงานมีสิทธิลาป่วยได้ตามที่ระบุในข้อบังคับการทำงานบริษัทซึ่งไม่ขัดต่อกฎหมายแรงงานไทย
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">5.2</div>
                <div class="detail">
                    <U>วันหยุดตามเพณี</U> พนักงานมีสิทธิได้รับค่าจ้างในวันหยุดตามเพณีตามประกาศของบริษัทซึ่งไม่ขัดต่อกฎหมายแรงงานไทย
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">5.3</div>
                <div class="detail">
                    <U>วันหยุดพักผ่อนประจำปี</U> พนักงานมีสิทธิหยุดพักผ่อนประจำปีตามที่ระบุในนโยบายและข้อบังคับการทำงานบริษัท
                </div>
            </div>

            <!------------------- ส่วนที่ 6 --------------------->
            <div class="flex_detail_container main">
                <div class="num">6.</div>
                <div class="detail">ค่าสินไหมทดแทนและค่าเสียหาย</div>
            </div>
            <div class="flex_detail_container">
                <div class="num"></div>
                <div class="detail">
                    พนักงานจะป้องกันไม่ให้เกิดความเสียหายและข้อเรียกร้องใดๆ กับบริษัท อันเกิดจากความผิดพลาดในการปฏิบัติหน้าที่หรือการกระทำของพนักงานนอกขอบเขตหรือเกินขอบเขตการทำงานตามหน้าที่ของพนักงาน
                </div>
            </div>

            <div class="page-break"></div>

            <!------------------- ส่วนที่ 7 --------------------->
            <div class="flex_detail_container main">
                <div class="num">7.</div>
                <div class="detail">ระยะเวลาและการสิ้นสุดของสัญญา</div>
            </div>
            <div class="flex_detail_container">
                <div class="num">7.1</div>
                <div class="detail">
                    สัญญานี้มีผลตั้งแต่วันที่มีผลบังคับใช้ เว้นแต่มีการบอกเลิกสัญญาก่อน ตามที่ระบุในข้อ 7.3
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">7.2</div>
                <div class="detail">
                    พนักงานจะต้องคืนทรัพย์สินทั้งหมดของบริษัทที่อยู่ในความครอบครองของพนักงานตามที่บริษัทกำหนดเมื่อสัญญาสิ้นสุดลง
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">7.3</div>
                <div class="detail">
                    สัญญานี้จะสิ้นสุดลงเมื่อเกิดสถานการณ์ ดังต่อไปนี้
                    <div class="flex_detail_container">
                        <div class="num">7.3.1</div>
                        <div class="detail l3">
                            การเลิกสัญญาที่มีการบอกกล่าวล่วงหน้า<br>
                            หากคู่สัญญาฝ่ายใดประสงค์ที่จะให้สัญญาสิ้นสุดลงคู่สัญญาฝ่ายนั้นต้องส่งหนังสือแจ้งการเลิกสัญญาล่วงหน้าให้อีกฝ่าย ไม่น้อยกว่า 1 (หนึ่ง) งวดเงินเดือนก่อนสิ้นสุดสัญญาจ้าง เพื่อให้การเลิกสัญญามีผลบังคับใช้
                        </div>
                    </div>
                    <div class="flex_detail_container">
                        <div class="num">7.3.2</div>
                        <div class="detail l3">
                            การเลิกสัญญาที่ไม่มีการบอกกล่าวล่วงหน้า<br>
                            นอกจากที่กล่าวข้างต้น พนักงานอาจจะถูกปลดออกโดยไม่ต้องมีการบอกกล่าวล่วงหน้าตามที่ระบุในข้อบังคับการทำงานบริษัท
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">7.4</div>
                <div class="detail">
                    พนักงานจะต้องรับผิดในความสูญหายและเสียหายที่เกิดขึ้นกับบริษัทซึ่งเป็นเหตุให้เลิกสัญญา โดยเกิดจากการกระทำผิดของพนักงาน
                </div>
            </div>

            <!------------------- ส่วนที่ 8 --------------------->
            <div class="flex_detail_container main">
                <div class="num">8.</div>
                <div class="detail">ความสัมพันธ์ของคู่สัญญาและเจ้าของกรรมสิทธิ์ซอฟแวร์</div>
            </div>
            <div class="flex_detail_container">
                <div class="num">8.1</div>
                <div class="detail">
                    พนักงานยอมรับว่าสัญญานี้เป็นสัญญาที่ก่อให้เกิดความสัมพันธ์ในฐานะนายจ้างและลูกจ้างระหว่างบริษัทและพนักงานในช่วงระยะเวลาจ้างงาน ซึ่งเป็นไปตามความประสงค์ของทั้งสองฝ่าย โดยไม่ประสงค์จะก่อให้เกิดความสัมพันธ์รูปแบบอื่น
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">8.2</div>
                <div class="detail">
                    คู่สัญญาทั้งสองฝ่ายยอมรับว่า ซอฟแวร์ รหัสที่เครื่องคอมพิวเตอร์อ่านได้ และรหัสในการเขียนโปรแกรม กุญแจรหัสที่เครื่องคอมพิวเตอร์อ่านได้ และกุญแจป้องกันรหัสในการเขียนโปรแกรม และวัตถุอื่นๆ ที่เกี่ยวข้อง ข้อมูล การพิมพ์เอกสาร เอกสาร และรายงานที่ถูกพัฒนาขึ้นหรือใช้โดยพนักงานอันเป็นไปตามหน้าที่ของพนักงาน จะถือเป็นทรัพย์สินของบริษัทแต่เพียงผู้เดียว
                </div>
            </div>

            <!------------------- ส่วนที่ 9 --------------------->
            <div class="flex_detail_container main">
                <div class="num">9.</div>
                <div class="detail">การเก็บรักษาความลับ</div>
            </div>
            <div class="flex_detail_container">
                <div class="num">9.1</div>
                <div class="detail">
                    พนักงานตกลงและยอมรับว่า ข้อมูลทั้งหมดหรือบางส่วนที่พนักงานได้รับมาระหว่างช่วงเวลาของการจ้างงานของบริษัท หรืองานที่มีความเกี่ยวข้องกับการจ้างงานของพนักงาน จะถือเป็นทรัพย์สินของบริษัทแต่เพียงผู้เดียวซึ่งถือเป็นความลับของบริษัท โดยรวมถึงรายการดังต่อไปนี้
                    <div class="flex_detail_container">
                        <div class="num">9.1.1</div>
                        <div class="detail l3">
                            เครื่องมือในการพัฒนาซอฟแวร์ทุกประเภท รหัสในการเขียนโปรแกรม รหัสที่เครื่องคอมพิวเตอร์อ่านได้ ข้อมูลทางเทคนิค และไฟล์คอมพิวเตอร์
                        </div>
                    </div>
                    <div class="flex_detail_container">
                        <div class="num">9.1.2</div>
                        <div class="detail l3">
                            ข้อมูลที่บรรจุอยู่ในเครื่องมือซอฟแวร์ ข้อมูลทางเทคนิค ไฟล์คอมพิวเตอร์ บันทึก จดหมายโต้ตอบ คู่มือ แบบฟอร์ม รายชื่อที่อยู่ แบบฟอร์มทางการเงิน งบการเงิน และบันทึกอื่นๆ รวมถึงเอกสารและไฟล์ข้อมูล
                        </div>
                    </div>
                    <div class="page-break"></div>
                    <div class="flex_detail_container">
                        <div class="num">9.1.3</div>
                        <div class="detail l3">
                            ข้อมูลเฉพาะของทุกโครงการรวมถึงรายละเอียดทางการเงิน แบบ ราคา หุ้นส่วน ผู้เกี่ยวข้อง ที่ปรึกษา ลูกค้า ผู้รับบริการ ผู้รับเหมา ผู้รับเหมาช่วง คู่ค้า และอื่นๆ ไม่ว่าจะเป็นโครงการในอดีต ปัจจุบัน และอนาคต หรือโครงการที่เพิ่งเริ่มต้นที่บริษัทเข้าไปมีส่วนเกี่ยวข้องด้วย
                        </div>
                    </div>
                    พนักงานจะไม่ใช้ ทำสำเนา และ/หรือเปิดเผยข้อมูลข้างต้นต่อบุคคลที่สามโดยไม่ได้รับอนุญาตล่วงหน้าเป็นลายลักษณ์อักษรจากบริษัท
                </div>
            </div>
            <div class="flex_detail_container">
                <div class="num">9.2</div>
                <div class="detail">
                    พนักงานตกลงและยอมรับว่าการเปิดเผย ลบออก หรือทำสำเนาข้อมูลโดยไม่ได้รับอนุญาตเป็นลายลักษณ์อักษรจากบริษัท ถือเป็นเงื่อนไขในการเลิกสัญญาได้ และบริษัทมีสิทธิดำเนินการใดๆ ตามกฎหมาย หากเกิดความเสียหายขึ้น
                </div>
            </div>

            <!------------------- ส่วนที่ 10 --------------------->
            <div class="flex_detail_container main">
                <div class="num">10.</div>
                <div class="detail">การห้ามโอนสิทธิของพนักงาน</div>
            </div>

            <div class="flex_detail_container">
                <div class="num"></div>
                <div class="detail">
                    บริษัทและพนักงานตกลงและยอมรับว่า บริษัทว่าจ้างพนักงานให้ปฏิบัติหน้าที่นี้ด้วยตนเอง
                    ซึ่งพนักงานไม่สามารถมอบหมายให้ผู้รับมอบอำนาจหรือตัวแทนกระทำการแทนได้
                    กล่าวคือ พนักงานไม่มีสิทธิมอบหมายให้ผู้อื่นปฏิบัติหน้าที่แทนได้ตามสัญญานี้
                </div>
            </div>

            <!------------------- ส่วนที่ 11 --------------------->
            <div class="flex_detail_container main">
                <div class="num">11.</div>
                <div class="detail">ห้ามค้าแข่ง</div>
            </div>

            <div class="flex_detail_container">
                <div class="num"></div>
                <div class="detail">
                    พนักงานจะต้องไม่ไปปฏิบัติงานในธุรกิจที่เป็นคู่แข่งกับธุรกิจของบริษัท
                    เป็นระยะเวลาอย่างน้อย 1 (หนึ่ง) ปี หลังจากที่สัญญาสิ้นสุดลง
                </div>
            </div>

            <!------------------- ส่วนที่ 12 --------------------->
            <div class="flex_detail_container main">
                <div class="num">12.</div>
                <div class="detail">การเปิดเผยข้อมูลการขัดกันของผลประโยชน์</div>
            </div>

            <div class="flex_detail_container">
                <div class="num"></div>
                <div class="detail">
                    พนักงานจะเปิดเผยข้อมูลทั้งหมดแก่บริษัทในเรื่องการขัดกันของผลประโยชน์
                    การประเมินการขัดกันของผลประโยชน์ที่มีส่วนเกี่ยวข้องกับหน้าที่
                    หรือข้อผูกพันของพนักงานภายใต้สัญญานี้
                </div>
            </div>

            <!------------------- ส่วนที่ 13 --------------------->
            <div class="flex_detail_container main">
                <div class="num">13.</div>
                <div class="detail">เบ็ดเตล็ด</div>
            </div>

            <div class="flex_detail_container">
                <div class="num">13.1</div>
                <div class="detail">
                    <u>การบอกกล่าว</u> การบอกกล่าวใดๆ จะต้องส่งเป็นลายลักษณ์อักษรให้คู่สัญญาอีกฝ่าย
                    ด้วยการส่วนตัว ลงทะเบียน หรือการส่งที่มีการรับรองการส่ง
                    ไปรษณีย์ที่ชำระล่วงหน้า ไปถึงที่อยู่ของคู่สัญญาฝ่ายที่ต้องการให้การบอกกล่าวไปถึง
                    ตามหลักฐานการส่ง
                </div>
            </div>

            <div class="flex_detail_container">
                <div class="num">13.2</div>
                <div class="detail">
                    <u>สัญญาโมฆะบางส่วน</u> หากบางส่วนของสัญญาเป็นโมฆะ ไม่ชอบด้วยกฎหมาย
                    หรือไม่มีผลบังคับใช้ในข้อตกลง เงื่อนไขหรือบทบัญญัติ
                    ส่วนที่เหลือของข้อตกลง เงื่อนไขหรือบทบัญญัติในสัญญานี้
                    นอกเหนือจากที่กล่าวข้างต้น จะยังสมบูรณ์และมีผลบังคับใช้ได้ทั้งหมดโดยชอบด้วยกฎหมาย
                    หากบทบัญญัติใดเป็นโมฆะหรือไม่มีผลบังคับใช้ได้ในเฉพาะบางสถานการณ์
                    ส่วนอื่นให้ถือว่ายังคงบังคับใช้ได้ทั้งหมดในทุกสถานการณ์
                </div>
            </div>

            <div class="flex_detail_container">
                <div class="num">13.3</div>
                <div class="detail">
                    <u>การสละสิทธิ</u> หากคู่สัญญาฝ่ายใดฝ่ายหนึ่งไม่บังคับใช้หรือไม่ใช้สิทธิ
                    หรือสิทธิตามข้อตกลงที่มีตามสัญญานี้
                    จะไม่ถือว่าคู่สัญญาฝ่ายนั้นสละสิทธิบังคับใช้หรือใช้สิทธิ
                    หรือสิทธิที่มีตามข้อตกลงในภายหลัง
                </div>
            </div>

            <div class="flex_detail_container">
                <div class="num">13.4</div>
                <div class="detail">
                    <u>ผู้สืบสันดานและผู้รับมอบอำนาจ</u>
                    สัญญานี้ก่อให้เกิดผลผูกพันต่อผู้สืบสันดาน
                    และผู้ดูแลผลประโยชน์ของบริษัท
                </div>
            </div>

            <div class="page-break"></div>

            <div class="flex_detail_container">
                <div class="num">13.5</div>
                <div class="detail">
                    <u>กฎหมายที่บังคับใช้</u>
                    สัญญานี้บังคับใช้และตีความภายใต้กฎหมายไทย
                </div>
            </div>

            <div class="flex_detail_container">
                <div class="num">13.6</div>
                <div class="detail">
                    <u>สัญญามีสองภาษา</u>
                    หากสัญญานี้ทำขึ้นเป็นภาษาไทยและภาษาอังกฤษ
                    ในกรณีที่เนื้อความของทั้งสองฉบับไม่สอดคล้องกัน
                    ให้ฉบับภาษาไทยมีผลผูกพันระหว่างคู่สัญญา
                </div>
            </div>

            <!------------------- สรุปสัญญา --------------------->
            <div class="flex_detail_container text-indent" style="margin-top: 15px;">
                <div class="num"></div>
                <div class="detail" style="margin-top: 15px;">
                    สัญญานี้ จัดทำขึ้นเป็นสองฉบับ มีข้อความถูกต้อง ตรงกัน คู่สัญญาทั้งสองฝ่ายต่างถือไว้ฝ่ายละหนึ่งฉบับ
                </div>
            </div>
            <div class="flex_detail_container text-indent" style="margin-top: 15px;">
                <div class="num"></div>
                <div class="detail">
                    เพื่อเป็นหลักฐานผู้ลงนามข้างล่างนี้ เข้าใจข้อความในสัญญานี้โดยตลอด จึงลงลายมือชื่อ และประทับตรา (ถ้ามี) ไว้ต่อหน้าพยาน
                </div>
            </div>
            <div class="flex_detail_container" style="margin-top: 15px;">
                <div class="num"></div>
                <div class="detail">
                    <div class="text-indent">ในนามของ</div>
                    <div class="text-indent"><B>{{$companyName}}</B></div>
                </div>
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
                    (..............................................................)<br>
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
        <div class="page-break"></div>

        <!---------------------เอกสารแนบท้าย-------------------------->

        <div class="header-section">
            <div class="header-left">
                <!-- Element ซ้าย -->
            </div>
            <div class="header-center">
                <div class="main-title">เอกสารแนบท้าย</div>
            </div>
            <div class="header-right">
                <!-- Element ขวา -->
            </div>
        </div>
        <div class="intro-section">
            <div class="text-indent">1. ใบพรรณางาน (Job Description)</div>
            <div class="text-indent">2. จรรยาบรรณในการทำธุรกิจ (Code of Conduct)</div>
            <div class="text-indent">3. คู่มือพนักงาน (Employee Handbook)</div>
        </div>
    </div>
</body>


<!----------Java Script------------->
<script>
    function downloadPdf() {
        document.getElementById('button').style.display = "none";
        window.print();
        document.getElementById('button').style.display = "block";
        //location.replace('/');
    }
</script>