@php
use App\Filament\Components\ActionFormComponent;
use Filament\Support\Colors\Color;
@endphp

<div>
    <x-filament-actions::group
        :actions="[
            $this->applicantPdfAction,
            $this->employmentPdfAction,
            $this->nonDisclosurePdfAction,
            ]"
        label="ดาวน์โหลดเอกสาร"
        tooltip="ดาวน์โหลดเอกสาร"
        icon="heroicon-m-document-arrow-down"
        color="primary"
        size="xl"
        iconSize="xl"
        dropdown-placement="bottom-end"
        button=true
        :extraAttributes="['style' => 'font-size: 1.2rem; width: 100%;']"
    />
    <x-filament-actions::modals />
</div>
