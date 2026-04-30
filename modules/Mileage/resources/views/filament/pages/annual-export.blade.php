<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Recherche des déclarations
        </x-slot>
        <x-slot name="description">
            Sélectionnez l'année, le département et le statut omnium pour générer un récapitulatif annuel.
        </x-slot>

        <form wire:submit="search">
            {{ $this->form }}
            <div class="mt-6">
                <x-filament::button type="submit">
                    <span>Rechercher</span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if($searched)
        <x-filament::section>
            <x-slot name="heading">
                Résultats
            </x-slot>

            @if($declarations->isEmpty())
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Aucune déclaration trouvée pour les critères sélectionnés.
                </div>
            @else
                <div class="mt-6 flex justify-end">
                    <x-filament::button
                        wire:click="downloadPdf"
                        wire:loading.attr="disabled"
                        color="success"
                        icon="tabler-download"
                    >
                        <x-filament::loading-indicator wire:loading wire:target="downloadPdf" class="h-5 w-5" />
                        <span wire:loading.remove wire:target="downloadPdf">Télécharger le PDF</span>
                        <span wire:loading wire:target="downloadPdf">Génération...</span>
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
