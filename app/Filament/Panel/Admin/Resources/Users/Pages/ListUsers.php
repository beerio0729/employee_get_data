<?php

namespace App\Filament\Panel\Admin\Resources\Users\Pages;

use Livewire\Component;
use Filament\Actions\Action;
use App\Jobs\RefreshInterviewStatusJob;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\WorkStatusDefination\WorkStatusDefination;
use App\Filament\Panel\Admin\Resources\Users\UserResource;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;

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
                ->visible(fn($livewire) => (int) $livewire->tableFilters['filter_component']['status_detail_id'] === self::updateStatusId('interview_scheduled')) //นัดสัมภาษณ์แล้ว
                ->action(function ($livewire) {
                    RefreshInterviewStatusJob::dispatch();
                    Notification::make()
                        ->title("คัดกรองคนไม่มาสัมภาษณ์เรียบร้อยแล้ว")
                        ->success()
                        ->send();
                    $livewire->tableFilters['filter_component']['status_detail_id'] = self::updateStatusId('no_interviewed');
                    $livewire->tableFilters['filter_component']['start_interview_at'] = null;
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

    public function getTabs(): array
    {   
        $workSate = WorkStatusDefination::all();

        if ($workSate->count() === 1) {
            return [];
        }
        
        return [
            'all' => Tab::make()
                ->icon('heroicon-m-user-group')
                ->label('All'),
            ...$this->tabFilterComponent($workSate)

        ];
    }

    public function tabFilterComponent($workSate): array
    {
        $tabs = [];
        foreach ($workSate->toArray() as $workSate) {
            $tabs[$workSate['code']] =
                Tab::make()
                ->label($workSate['name_th'])
                ->modifyQueryUsing(fn(Builder $query) =>
                $query->whereRelation(
                    'userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail.workStatusDefDetailBelongsToWorkStatusDef',
                    'code',
                    $workSate['code']
                ));
        }
        return $tabs;
    }

    // public function getDefaultActiveTab(): string | int | null
    // {
    //     return 'active';
    // }


    public function updatedActiveTab(): void
    {
        parent::updatedActiveTab();

        $this->tableFilters['filter_component']['status_detail_id'] = null;
        $this->tableFilters['filter_component']['start_interview_at'] = null;
        $this->tableFilters['filter_component']['positions_id'] = null;
    }

    public static function updateStatusId($status): int
    {
        return WorkStatusDefinationDetail::where('code', $status)->first()->id;
    }
}
