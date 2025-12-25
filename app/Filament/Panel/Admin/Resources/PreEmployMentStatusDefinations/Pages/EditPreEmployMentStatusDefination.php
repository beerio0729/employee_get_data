<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages;

use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\PreEmployMentStatusDefinationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPreEmployMentStatusDefination extends EditRecord
{
    protected static string $resource = PreEmployMentStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //DeleteAction::make(),
        ];
    }
}
