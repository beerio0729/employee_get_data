@php
use App\Filament\Components\ActionFormComponent;
@endphp

<div>
    <x-filament-actions::group
        :actions="[
            $this->image_profile(),
            $this->idcard,
            $this->resume,
            $this->transcript,
            $this->military,
            $this->marital,
            $this->certificate,
            $this->anotherDoc,
            ]"
        label="อับโหลดเอกสาร"
        tooltip="อับโหลดเอกสาร"
        icon="heroicon-m-document-arrow-up"
        color="success"
        size="xl"
        iconSize="xl"
        dropdown-placement="top-start"
        button=true
        dropdownPlacement='top-start'
        :extraAttributes="['style' => 'font-size: 1.2rem; width: 100%;']"
    />
    <x-filament-actions::modals />
</div>