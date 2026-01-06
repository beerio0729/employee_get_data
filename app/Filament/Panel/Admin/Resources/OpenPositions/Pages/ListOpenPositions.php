<?php

namespace App\Filament\Panel\Admin\Resources\OpenPositions\Pages;

use App\Filament\Panel\Admin\Resources\OpenPositions\OpenPositionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpenPositions extends ListRecords
{
    protected static string $resource = OpenPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
