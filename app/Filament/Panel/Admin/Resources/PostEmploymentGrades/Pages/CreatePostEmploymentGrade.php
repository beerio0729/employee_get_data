<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Models\WorkStatus\PostEmploymentGrade;
use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\PostEmploymentGradeResource;

class CreatePostEmploymentGrade extends CreateRecord
{
    protected static string $resource = PostEmploymentGradeResource::class;

    // protected function handleRecordCreation(array $data): Model
    // {
    //     foreach ($data['grade_emp'] as $grade) {
    //         $record = PostEmploymentGrade::create($grade);
    //     }

    //     return $record;
    // }

    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
