<?php

namespace App\Filament\Panel\Admin\Resources\OrganizationLevels\Pages;

use Filament\Actions\Action;

use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Models\Organization\OrganizationLevel;
use App\Filament\Panel\Admin\Resources\OrganizationLevels\OrganizationLevelResource;

class ListOrganizationLevels extends ListRecords
{
    protected static string $resource = OrganizationLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('clearAllOrgLevelCache')
                ->label('Refresh ข้อมูล')
                ->color('warning')
                ->action(function ($livewire) {
                    $levels = OrganizationLevel::pluck('level'); // จะได้ collection: ['first','second',...]

                    foreach ($levels as $level) {
                        cache()->forget('org_level_collection_' . $level);
                        cache()->forget('org_level_id_' . $level);
                    }
                    
                    Notification::make()
                        ->title('Refresh ข้อมูลเรียบร้อยแล้ว')
                        ->success()
                        ->send();
                    return redirect($this->getResource()::getUrl('index'));
                })

        ];
    }

}
