<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Selectionner la date
        </x-slot>

        <form wire:submit.prevent="$refresh">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit" icon="tabler-refresh">
                    Rafraichir la liste
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Courriers a notifier ({{ $this->getTableRecords()->count() }})
        </x-slot>

        {{ $this->table }}
    </x-filament::section>

</x-filament-panels::page>
