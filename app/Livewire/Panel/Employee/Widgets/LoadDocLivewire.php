<?php

namespace App\Livewire\Panel\Employee\Widgets;

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

    public function applicantPdfAction(): Action
    {
        return (new ActionFormComponent())->applicantPdfAction();
    }
    
    public function employmentPdfAction(): Action
    {
        return (new ActionFormComponent())->employmentPdfAction();
    }

    public function nonDisclosurePdfAction(): Action
    {
        return (new ActionFormComponent())->nonDisclosurePdfAction();
    }

    public function render()
    {
        return view('livewire.panel.employee.widgets.load-doc-livewire');
    }
}
