<?php

namespace App\Filament\Panel\Admin\Resources\Users\Pages;

use App\Jobs\noInterviewJob;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
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
            Action::make('no_interview_action')
                ->color('danger')
                ->tooltip(new HtmlString('หากกดปุ่มนี้ คนที่วันสัมภาษณ์เลยเวลาปัจจุบันไปแล้ว หากไม่ได้รับสถานะว่า "มาสัมภาษณ์แล้ว" 
                จะได้รับสถานะเป็น<br><br><B>"ไม่มาสัมภาษณ์"</B><br><br>*โปรดระมัดระวังในการใช้ปุ่มนี้โดยที่ต้องมั่นใจว่า คนที่มาสัมภาษณ์ตามเวลา ได้รับสถานะว่า "สัมภาษณ์แล้ว" ครบทุกคน'))
                ->label('คัดกรองคนไม่มาสัมภาษณ์')
                ->visible(fn($livewire) => $livewire->activeTab === 'applicant')
                ->action(function ($livewire) {
                    noInterviewJob::dispatch();
                    Notification::make()
                        ->title("คัดกรองคนไม่มาสัมภาษณ์เรียบร้อยแล้ว")
                        ->success()
                        ->send();
                    $livewire->tableFilters['filter_component']['status_detail_id'] = WorkStatusDefinationDetail::statusId('no_interviewed');
                    $livewire->tableFilters['filter_component']['start_filter'] = null;
                    $livewire->tableFilters['filter_component']['end_filter'] = null;
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
        $this->tableFilters['filter_component']['start_filter'] = null;
        $this->tableFilters['filter_component']['end_filter'] = null;
        $this->tableFilters['filter_component']['positions_id'] = null;
    }

    public function getTitle(): string | Htmlable
    {
        return $this->activeTab === 'all'
            ? 'บุคคลากรทั้งหมด'
            : WorkStatusDefination::where('code', $this->activeTab)->value('name_th');
    }
}
