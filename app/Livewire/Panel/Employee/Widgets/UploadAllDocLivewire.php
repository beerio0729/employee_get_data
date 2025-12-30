<?php

namespace App\Livewire\Panel\Employee\Widgets;

use Livewire\Component;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use App\Filament\Components\ActionFormComponent;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class UploadAllDocLivewire extends Component implements HasActions, HasSchemas
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

    public function image_profile(): Action
    {
        return (new ActionFormComponent())->imageProfile();
    }

    public function idcard(): Action
    {
        return (new ActionFormComponent())->idcardAction();
    }

    public function resume(): Action
    {
        return (new ActionFormComponent())->resumeAction();
    }

    public function transcript(): Action
    {
        return (new ActionFormComponent())->transcriptAction();
    }

    public function military(): Action
    {
        return (new ActionFormComponent())->militaryAction();
    }

    public function marital(): Action
    {
        return (new ActionFormComponent())->maritalAction();
    }

    public function certificate(): Action
    {
        return (new ActionFormComponent())->certificateAction();
    }

    public function another(): Action
    {
        return (new ActionFormComponent())->anotherDocAction();
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


    /**********************************************/
    public function render()
    {
        return view('livewire.panel.employee.widgets.upload-all-doc-livewire');
    }
}
