<?php

namespace App\Filament\Pages;

use Detection\MobileDetect;
use Filament\Actions\Action;
use App\Events\ProcessEmpDocEvent;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Components\ActionFormComponent;


class Dashboard extends BaseDashboard
{
    public ?string $confirm = '
        <div>
            ฉันยอมยินยอมให้บริษัทนำข้อมูลของเก็บไว้เพื่อพิจารณาการรับสมัครงาน<br>
            <a 
            style="text-decoration: underline;"
            href="http://127.0.0.1" 
            target="_blank"
            >
                ดูรายละเอียดเงื่อนไข
            </a>
        </div>
    ';
    public $modal = [
        'is_open' => false,
        'action_id' => null
    ];

    protected $listeners = [
        'openActionModal' => 'openActionModal',
        'refreshActionModal' => 'refreshActionModal',
    ];

    public bool $isSubmitDisabledFromFile = true;
    public bool $isSubmitDisabledFromConfirm = true;
    public bool $isMobile;
    public bool $isAndroidOS;
    
    public function getColumns(): int | array
{
    return [
        'sm' => 1,
    ];
}

    public function updateStateInFile($value)
    {
        $this->isSubmitDisabledFromFile = $value; // Disable if blank
    }

    public function updateStateInConfirm($value)
    {   //dump(!$value);
        $this->isSubmitDisabledFromConfirm = !$value;
    }

    public function getDocEmp($record, $action)
    {
        return $record->userHasmanyDocEmp()->where('file_name', $action->getName());
    }


    /************************************** */
    public function getHeaderActions(): array
    {
        $detect = new MobileDetect();
        $this->isMobile = $detect->isMobile();
        $this->isAndroidOS = $detect->isAndroidOS();
        return [
            (new ActionFormComponent())->uploadAllDocActionGroup(),
            (new ActionFormComponent())->addtionalAction(),
            Action::make('pdf')
                ->record(auth()->user())
                ->hidden(fn() => $this->isMobile ? 1 : 0)
                ->label('ดาวน์โหลดใบสมัคร')
                ->icon('heroicon-m-document-arrow-down')
                ->color('info')
                ->url(fn() =>
                blank($this->checkDocDownloaded()['upload']) &&
                    blank($this->checkDocDownloaded()['input'])
                    ? '/pdf'
                    : null)
                ->action(function ($record) {
                    $missing = $this->checkDocDownloaded();
                    $parts = [];

                    $hasUpload = !blank($missing['upload']);
                    $hasInput  = !blank($missing['input']);

                    if ($hasUpload) {
                        $parts[] = 'คุณยังไม่ได้อัปโหลดเอกสาร: <br>"' . implode(', ', $missing['upload']) . '"';
                    }

                    if ($hasInput) {
                        $parts[] = 'คุณยังไม่ได้กรอกข้อมูลเพิ่มเติมในหัวข้อ: <br>"' . implode(', ', $missing['input']) . '"';
                    }

                    // ประโยคปิดท้าย
                    if ($hasUpload && $hasInput) {
                        $ending = 'กรุณาอัปโหลดเอกสาร และ กรอกข้อมูลดังกล่าว<br>ก่อนดาวน์โหลดใบสมัคร';
                        $msg = implode('<br><br>', $parts) . '<br><br>' . $ending;
                        event(new ProcessEmpDocEvent($msg, $record, 'popup', null, false));
                    }
                    if ($hasUpload) {
                        $ending = 'กรุณาอัปโหลดเอกสารก่อนดาวน์โหลดใบสมัคร';
                        $msg = implode('<br><br>', $parts) . '<br><br>' . $ending;
                        event(new ProcessEmpDocEvent($msg, $record, 'popup', null, false));
                    }
                    if ($hasInput) {
                        $ending = 'กรุณากรอกข้อมูลดังกล่าวก่อนดาวน์โหลดใบสมัคร';
                        $msg = implode('<br><br>', $parts) . '<br><br>' . $ending;
                        event(new ProcessEmpDocEvent($msg, $record, 'popup', null, false));
                    }
                })
                ->openUrlInNewTab()
                ->button(),

        ];
    }

    /*****************เกี่ยวกับ Mount Action******************* */
    public function openActionModal($id = null)
    {
        $this->mountAction($id);
    }

    public function refreshActionModal($id = null)
    {
        $this->unmountAction();
        $this->mountAction($id);
    }

    /****************ฟังก์ชั่นพิเศษสำหรับเช็คว่าโหลดเอกสารหรือยัง***************** */
    public function checkDocDownloaded()
    {
        $user = auth()->user();

        // ดึง prefix จากบัตรประชาชน (ถ้ามี)
        $prefix = $user->userHasoneIdcard?->prefix_name_en;
        // ตรวจว่าเป็นผู้หญิงหรือไม่
        $isFemale = in_array(trim(strtolower($prefix), "."), ['miss', 'mrs']);
        $errorUplaod = [ //สำหรับเอกสารอับโหลด
            'resume'        => $user->userHasoneResume()->exists(),
            'บัตรประชาชน'   => $user->userHasoneIdcard()->exists(),
            'วุฒิการศึกษา'   => $user->userHasmanyTranscript()->exists(),
        ];

        $additional = $user->userHasoneAdditionalInfo;

        $errorInput = [ //สำหรับข้อมูลที่ต้องกรอกเอง
            'บิดา' => blank($user->userHasoneFather->name),
            'มารดา' => blank($user->userHasoneMother->name),
            'ผู้ติดต่อยามฉุกเฉิน' => blank($additional->emergency_name),
            'คำถามสุขภาพ' => blank($additional->medical_condition),
            'คำถามเพิ่มเติม' => blank($additional->know_someone),
        ];

        // ใส่ใบเกณฑ์ทหารเฉพาะกรณี "ไม่ใช่ผู้หญิง"
        if (!$isFemale) {
            $errorUplaod['ใบเกณฑ์ทหาร'] = $user->userHasoneMilitary()->exists();
        }

        // หาเฉพาะรายการที่ยังไม่มีไฟล์
        $missingUplaod = array_keys(array_filter($errorUplaod, fn($v) => $v === false));
        $missingInput = array_keys(array_filter($errorInput, fn($v) => $v === true));
        return [
            'upload' => $missingUplaod,
            'input' => $missingInput,
        ];
    }

    public function fieldsteLabel($state)
    {
        $text = "ข้อมูลคู่สมรส";
        $icon = "⚠️"; // หรือ SVG icon
        $warning = "<div style='color: #FFA500; font-weight: bold;'>{$icon} คุณยังไม่ได้กรอกข้อมูลคู่สมรส</div>";
        return empty($state['alive']) ? $text . $warning : $text;
    }
}
