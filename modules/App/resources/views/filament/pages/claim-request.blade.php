<x-filament-panels::page>
    <x-filament::callout
        icon="heroicon-o-information-circle"
        color="info"
    >
        <x-slot name="heading">
            Important Notice
        </x-slot>

        <x-slot name="description">
            "Ce formulaire vous permet de générer une créance type.
            Un pdf sera créé vous ne devrez plus que l'imprimer,
            le signer et le faire parvenir au service de la Recette communale,
            Bld du Midi 22 à 6900 Marche-en-Famenne"
        </x-slot>
    </x-filament::callout>

   <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Enregistrer
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
