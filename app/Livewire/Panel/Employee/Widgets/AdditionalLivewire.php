<?php

namespace App\Livewire\Panel\Employee\Widgets;

use Livewire\Component;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use App\Filament\Components\ActionFormComponent;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class AdditionalLivewire extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public $modal = [
        'is_open' => false,
        'action_id' => null
    ];

    protected $listeners = [
        'openActionModal' => 'openActionModal',
        'refreshActionModal' => 'refreshActionModal',
    ];

    public function info(): Action
    {
        return (new ActionFormComponent())->addtionalAction();
    }


    public function render()
    {
        return view('livewire.panel.employee.widgets.additional-livewire');
    }

    /*****************เกี่ยวกับ Mount Action******************* */
    public function openActionModal($id = null)
    {
        $this->mountAction($id);
        $this->dispatch('refreshProfileWidget');
    }

    public function refreshActionModal($id = null)
    {
        $this->unmountAction();
        $this->mountAction($id);
        $this->dispatch('refreshProfileWidget');
    }
}
