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
        'closeActionModal' => 'closeActionModal',
    ];

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
            Action::make('refresh_status_interview')
                ->color('danger')
                ->label('คัดกรองคนไม่มาสัมภาษณ์')
                ->visible(fn($livewire) => $livewire->tableFilters['filter_component']['status_detail_id'] === '3' ? 1 : 0)
                ->action(function ($livewire) {
                    dispatch(new RefreshInterviewStatusJob());
                    Notification::make()
                        ->title("คัดกรองคนไม่มาสัมภาษณ์เรียบร้อยแล้ว")
                        ->success()
                        ->send();
                    $livewire->tableFilters['filter_component']['status_detail_id'] = 5;
                    $livewire->tableFilters['filter_component']['interview_at'] = null;
                }),
            Action::make('refresh_table')
                ->color('warning')
                ->label('Refresh ตาราง')
                ->action(function ($livewire) {
                    $livewire->dispatch('refresh');
                    Notification::make()
                        ->title("Refresh เรียบร้อยแล้ว")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function closeActionModal()
    {
        $this->unmountAction();
    }
}
