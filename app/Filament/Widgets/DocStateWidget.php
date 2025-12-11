<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocStateWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '2s';
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.doc-state-widget';
    protected function getStats(): array
    {
        $user = auth()->user();
        $doc = $user->userHasmanyDocEmp()->get()->toArray();
        $doc_count = count($doc);
        $additional = $this->countAdditional($user);
        $father = $this->countFather($user);
        $mother = $this->countMother($user);
        $siblig = $this->countSibling($user);



        $totalFields = $additional['field'] + $father['field'] + $mother['field']+$siblig['field']+8;
        $totalNull =  $additional['null'] + $father['null'] + $mother['null']+$siblig['null']+8-$doc_count;
        $suscess = $totalFields - $totalNull;

        $percent = intval(($suscess / $totalFields) * 100);
        $icon = $percent === 100 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle';

        return [
            Stat::make('', $doc_count . '/8')
                ->color(fn() => count($doc) === 8 ? 'success' : 'warning')
                ->descriptionIcon($icon)
                ->chart([5, 3, 1, 10, 2, 1, 8, 4, 3])
                ->description('เอกสารที่อับโหลดแล้ว'),
            Stat::make('', $percent . ' %')
                ->descriptionIcon($icon)
                ->color(fn() => $percent === 100 ? 'success' : 'warning')
                ->chart([17, 12, 20, 3, 25, 14, 50, 1])
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
}
