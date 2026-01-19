<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Pages;

use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\PostEmploymentGradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostEmploymentGrades extends ListRecords
{
    protected static string $resource = PostEmploymentGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
