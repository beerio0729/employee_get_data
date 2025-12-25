<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Pages;

use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\PostEmploymentStatusDefinationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostEmploymentStatusDefination extends EditRecord
{
    protected static string $resource = PostEmploymentStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //DeleteAction::make(),
        ];
    }
}
