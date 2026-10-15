<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Enregistrer
            </x-filament::button>

            <x-filament::button tag="a" :href="$this->getCancelUrl()" color="gray">
                Annuler
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
