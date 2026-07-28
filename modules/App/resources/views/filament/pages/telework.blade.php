<x-filament-panels::page>
    @if ($this->record)
        {{ $this->validationInfolist }}
    @endif

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Enregistrer
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
