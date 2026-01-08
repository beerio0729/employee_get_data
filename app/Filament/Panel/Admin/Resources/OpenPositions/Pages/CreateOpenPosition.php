<?php

namespace App\Filament\Panel\Admin\Resources\OpenPositions\Pages;

use App\Filament\Panel\Admin\Resources\OpenPositions\OpenPositionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpenPosition extends CreateRecord
{
    protected static string $resource = OpenPositionResource::class;
    
    protected function getRedirectUrl(): string
    {
        // redirect ไปหน้า list ของ resource
        return $this->getResource()::getUrl('index');
    }
}
