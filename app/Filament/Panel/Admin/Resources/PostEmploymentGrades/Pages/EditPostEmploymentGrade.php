<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Pages;

use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Models\WorkStatus\PostEmploymentGrade;
use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\PostEmploymentGradeResource;

class EditPostEmploymentGrade extends EditRecord
{
    protected static string $resource = PostEmploymentGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // protected function fillForm(): void
    // {
    //     // ดึงข้อมูลทั้งหมดจาก DB
    //     $grades = PostEmploymentGrade::query()
    //         ->orderBy('grade')
    //         ->get();

    //     // แปลงให้อยู่รูปแบบที่ Repeater ต้องการ
    //     $data = [
    //         'grade_emp' => $grades->map(fn($g) => [
    //             'grade'   => $g->grade,
    //             'name_th' => $g->name_th,
    //             'name_en' => $g->name_en,
    //         ])->toArray(),
    //     ];

    //     // fill ลงฟอร์ม
    //     $this->form->fill($data);
    // }

    // protected function handleRecordUpdate(Model $record, array $data): Model
    // {
    //     foreach ($data['grade_emp'] as $grade) {
    //         PostEmploymentGrade::updateOrCreate(
    //             ['grade' => $grade['grade'] ?? null],
    //             $grade
    //         );
    //     }

    //     return $record; // Filament ต้องการ Model กลับ
    // }

    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
