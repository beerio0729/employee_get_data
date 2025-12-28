<?php

namespace App\Filament\Panel\Admin\Resources\OrganizationLevels\Pages;

use App\Filament\Panel\Admin\Resources\OrganizationLevels\OrganizationLevelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationLevel extends EditRecord
{
    protected static string $resource = OrganizationLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
