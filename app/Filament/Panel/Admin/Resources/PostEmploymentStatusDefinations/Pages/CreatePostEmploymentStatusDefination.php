<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Pages;


use Filament\Resources\Pages\CreateRecord;
use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\PostEmploymentStatusDefinationResource;

class CreatePostEmploymentStatusDefination extends CreateRecord
{
    protected static string $resource = PostEmploymentStatusDefinationResource::class;

    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
