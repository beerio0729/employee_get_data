<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Pages;

use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\PostEmploymentStatusDefinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostEmploymentStatusDefinations extends ListRecords
{
    protected static string $resource = PostEmploymentStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('เพิ่มสถานะ'),
        ];
    }
}
