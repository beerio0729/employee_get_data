<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages;

use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\PreEmployMentStatusDefinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPreEmployMentStatusDefinations extends ListRecords
{
    protected static string $resource = PreEmployMentStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('เพิ่มสถานะ')->requiresConfirmation(),
        ];
    }
}
