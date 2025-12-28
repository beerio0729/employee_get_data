<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages;

use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\PreEmployMentStatusDefinationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePreEmployMentStatusDefination extends CreateRecord
{
    protected static string $resource = PreEmployMentStatusDefinationResource::class;

    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
