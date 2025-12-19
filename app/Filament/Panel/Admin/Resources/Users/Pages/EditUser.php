<?php

namespace App\Filament\Panel\Admin\Resources\Users\Pages;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Panel\Admin\Resources\Users\UserResource;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //DeleteAction::make(),
        ];
    }
}
