<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Selectionner la date
        </x-slot>

        <form wire:submit.prevent="loadPreviewData">
            {{ $this->form }}
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Courriers a notifier ({{ $this->getTableRecords()->count() }})
        </x-slot>

        {{ $this->table }}
    </x-filament::section>

</x-filament-panels::page>
