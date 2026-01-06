<?php

namespace App\Filament\Panel\Admin\Resources\OpenPositions\Pages;

use App\Filament\Panel\Admin\Resources\OpenPositions\OpenPositionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpenPosition extends EditRecord
{
    protected static string $resource = OpenPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
