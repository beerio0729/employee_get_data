<?php

namespace App\Livewire\WidgetLivewire;

use Livewire\Component;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use App\Filament\Components\ActionFormComponent;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class LoadDocLivewire extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function pdf(): Action
    {
        return (new ActionFormComponent())->downloadPDFForPhoneAction();
    }
    public function render()
    {
        return view('livewire.widget-livewire.load-doc-livewire');
    }
}
