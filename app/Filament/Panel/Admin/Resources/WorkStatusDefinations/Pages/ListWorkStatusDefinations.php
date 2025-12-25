<?php

namespace App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Pages;

use App\Filament\Panel\Admin\Resources\WorkStatusDefinations\WorkStatusDefinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkStatusDefinations extends ListRecords
{
    protected static string $resource = WorkStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
