<?php

namespace App\Filament\Panel\Admin\Resources\Users\Pages;

use Filament\Actions\Action;
use App\Jobs\RefreshInterviewStatusJob;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Panel\Admin\Resources\Users\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    protected $listeners = [
        'refresh' => '$refresh',
    ];

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
            Action::make('refresh_status_interview')
                ->label('อับเตดสถานะ')
                ->action(function () {
                    dispatch(new RefreshInterviewStatusJob());
                    Notification::make()
                        ->title("Refresh สถานะเรียบร้อยแล้ว")
                        ->success()
                        ->send();
                    $this->dispatch('refresh');
                })
        ];
    }
}
