<?php

namespace App\Filament\Panel\Admin\Resources\CompanyProfiles\Pages;

use App\Filament\Panel\Admin\Resources\CompanyProfiles\CompanyProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyProfiles extends ListRecords
{
    protected static string $resource = CompanyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn($model) => blank($model::get()->toArray())),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
