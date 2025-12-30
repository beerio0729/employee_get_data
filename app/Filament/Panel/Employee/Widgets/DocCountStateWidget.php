<?php

namespace App\Filament\Panel\Employee\Widgets;

use App\Services\CheckDocDownloaded;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocCountStateWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.panel.employee.widgets.doc-count-state-widget';
    protected function getStats(): array
    {
        $user = auth()->user();
        $doc = $user->userHasmanyDocEmp()->get()->toArray();
        $doc_count = count($doc);
        $additional = $this->countAdditional($user);
        $father = $this->countFather($user);
        $mother = $this->countMother($user);
        $siblig = $this->countSibling($user);



        $totalFields = $additional['field'] + $father['field'] + $mother['field'] + $siblig['field'] + 8;
        $totalNull =  $additional['null'] + $father['null'] + $mother['null'] + $siblig['null'] + 8 - $doc_count;
        $suscess = $totalFields - $totalNull;

        $doc_count_percent = intval(($doc_count / 8) * 100);
        $percent = intval(($suscess / $totalFields) * 100);
        $icon_count = $this->isSuccess($user)['upload_success'] ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle';
        $icon_percent = $this->isSuccess($user)['input_success'] ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle';

        return [
            Stat::make('', $doc_count . $this->countAllDoc($user))
                ->color(fn() => $this->isSuccess($user)['upload_success'] ? 'success' : 'warning')
                ->progress($doc_count_percent)
                ->descriptionIcon($icon_count)
                ->description('เอกสารที่อับโหลดแล้ว'),
            Stat::make('', $percent . ' %')
                ->descriptionIcon($icon_percent)
                ->color(fn() => $this->isSuccess($user)['input_success'] ? 'success' : 'warning')
                ->progress($percent)
                ->description('ความสมบูรณ์ของข้อมูล'),
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 2
        ];
    }

    public function countAdditional($user)
    {
        /***********ส่วนของการนับข้อมูลเพิ่มเติม*********** */
        $value = $user->userHasoneAdditionalInfo;
        $fillable = $value->getFillable();
        // เงื่อนไขแม่ → ฟิลด์ลูกที่จะลบถ้าแม่ == 0
        $conditionalFields = [
            'worked_company_before' => [
                'worked_company_detail',
                'worked_company_supervisor',
            ],
            'know_someone' => [
                'know_someone_name',
                'know_someone_relation',
            ],
            'medical_condition' => [
                'medical_condition_detail',
            ],
            'has_sso' => [
                'sso_hospital',
            ],
        ];

        $data = $value->only($fillable);

        // ลบฟิลด์ลูกที่ไม่เกี่ยว
        foreach ($conditionalFields as $parent => $children) {
            if (isset($data[$parent]) && $data[$parent] == 0) {
                foreach ($children as $child) {
                    unset($data[$child]);
                }
            }
        }

        // นับช่องทั้งหมดที่ควรใช้
        $CountFields = count($data);

        // นับช่องที่เป็น null
        $nullCount = collect($data)->filter(fn($v) => blank($v))->count();

        return ["field" => $CountFields, "null" => $nullCount];
    }

    public function countFather($user)
    {
        $value = $user->userHasoneFather;
        $fillable = $value->getFillable();
        $data = $value->only($fillable);
        $CountFields = count($data);

        // นับช่องที่เป็น null
        $nullCount = collect($data)->filter(fn($v) => blank($v))->count();

        return ["field" => $CountFields, "null" => $nullCount];
    }

    public function countMother($user)
    {
        $value = $user->userHasoneMother;
        $fillable = $value->getFillable();
        $data = $value->only($fillable);
        $CountFields = count($data);

        // นับช่องที่เป็น null
        $nullCount = collect($data)->filter(fn($v) => blank($v))->count();

        return ["field" => $CountFields, "null" => $nullCount];
    }

    public function countSibling($user)
    {
        $value = $user->userHasoneSibling;
        $fillable = $value->getFillable();
        $data = $value->only($fillable);
        $CountFields = count($data);

        // นับช่องที่เป็น null
        $nullCount = collect($data)->filter(fn($v) => blank($v))->count();

        return ["field" => $CountFields, "null" => $nullCount];
    }

    public function countAllDoc($user)
    {
        $gender = $user->userHasoneIdcard->gender;
        if ($gender === 'female') {
            $count = '/7';
        } else {
            $count = '/8';
        }

        return $count;
    }

    public function isSuccess($user): array
    {
        $missing = CheckDocDownloaded::check($user);

        return [
            'upload_success' => empty($missing['upload']),
            'input_success'  => empty($missing['input'])
        ];
    }
}
