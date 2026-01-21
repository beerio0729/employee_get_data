<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Pages;

use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use App\Models\WorkStatusDefination\WorkStatusDefination;
use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\PostEmploymentStatusDefinationResource;

class ListPostEmploymentStatusDefinations extends ListRecords
{
    protected static string $resource = PostEmploymentStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('เพิ่มสถานะ'),
        ];
    }

    public function getTabs(): array
    {
        $workSate = WorkStatusDefination::where('main_work_status', 'post_employment')->get();

        if ($workSate->count() === 1) {
            return [];
        }

        return [
            'all' => Tab::make()
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
                    'workStatusDefDetailBelongsToWorkStatusDef',
                    'code',
                    $workSate['code']
                ));
        }
        return $tabs;
    }
}
