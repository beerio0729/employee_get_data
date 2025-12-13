<?php

namespace App\Livewire\WidgetLivewire;

use Livewire\Component;
use Filament\Actions\Action;
use App\Events\ProcessEmpDocEvent;
use App\Filament\Components\ActionFormComponent;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class DownLoadPDFLivewire extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function pdf(): Action
    {
        return (new ActionFormComponent())->downloadPDFForPhoneAction();
    }


    public function render()
    {
        return view('livewire.widget-livewire.download-pdf-livewire');
    }
}
