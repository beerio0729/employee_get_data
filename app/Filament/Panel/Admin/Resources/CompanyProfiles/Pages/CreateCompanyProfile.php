<?php

namespace App\Filament\Panel\Admin\Resources\CompanyProfiles\Pages;

use App\Filament\Panel\Admin\Resources\CompanyProfiles\CompanyProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyProfile extends CreateRecord
{
    protected static string $resource = CompanyProfileResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
