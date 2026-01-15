@php
use App\Filament\Components\ActionFormComponent;
use Filament\Support\Colors\Color;
@endphp

<div>
    <x-filament-actions::group
        :actions="[
            $this->image_profile,
            $this->idcard,
            $this->resume,
            $this->transcript,
            $this->military,
            $this->marital,
            $this->certificate,
            $this->another,
            ]"
        label="อับโหลดเอกสาร"
        tooltip="อับโหลดเอกสาร"
        icon="heroicon-m-document-arrow-up"
        color="primary"
        size="xl"
        iconSize="xl"
        dropdown-placement="bottom-end"
        button=true
        :extraAttributes="['style' => 'font-size: 1.2rem; width: 100%;']"
    />
    <x-filament-actions::modals />
</div>