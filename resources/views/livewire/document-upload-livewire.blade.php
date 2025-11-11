<div>
    <form wire:submit="create">
        <x-filament::fieldset>
            <x-slot name="label">
                Address
            </x-slot>
            {{ $this->form }}
        </x-filament::fieldset>

        <x-filament::button
            wire:click="create"
            color="primary"
            size="sm">
            ส่งเอกสาร
        </x-filament::button>

        <x-filament::button
            color="danger"
            size="sm"
            tag="a"
            href="{{ env('APP_URL') }}">
            ยกเลิก
        </x-filament::button>

    </form>

    <x-filament-actions::modals />
</div>