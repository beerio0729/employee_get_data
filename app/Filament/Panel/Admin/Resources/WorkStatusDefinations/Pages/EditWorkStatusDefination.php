<?php

namespace App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Pages;

use App\Filament\Panel\Admin\Resources\WorkStatusDefinations\WorkStatusDefinationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkStatusDefination extends EditRecord
{
    protected static string $resource = WorkStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
