<x-filament-panels::page>
    @if ($this->record)
        {{ $this->validationInfolist }}
    @endif

    <form wire:submit="save">
        {{ $this->form }}

        @unless ($this->isLocked())
            <div class="mt-6">
                <x-filament::button type="submit">
                    Enregistrer
                </x-filament::button>
            </div>
        @endunless
    </form>
</x-filament-panels::page>
