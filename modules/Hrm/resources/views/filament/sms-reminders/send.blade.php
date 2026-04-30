<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Envoi du SMS via l'API Inforius
        </x-slot>
        <x-slot name="description">
            Vérifiez le numéro et le contenu du message avant l'envoi.
        </x-slot>

        <form wire:submit.prevent="send">
            {{ $this->form }}

            <div class="mt-6 flex flex-wrap gap-3">
                {{ $this->sendAction }}
                {{ $this->cancelAction }}
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
