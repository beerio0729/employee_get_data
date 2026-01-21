<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages;

use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use App\Models\WorkStatusDefination\WorkStatusDefination;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\PreEmployMentStatusDefinationResource;

class ListPreEmployMentStatusDefinations extends ListRecords
{
    protected static string $resource = PreEmployMentStatusDefinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('เพิ่มสถานะ')->requiresConfirmation(),
        ];
    }

    public function getTabs(): array
    {
        $workSate = WorkStatusDefination::where('main_work_status', 'pre_employment')->get();

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
