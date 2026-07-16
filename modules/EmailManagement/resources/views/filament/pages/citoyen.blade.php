<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Serveur citoyen</x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Tout se gère depuis le serveur citoyen.
        </p>

        <x-filament::link
            href="https://citoyen.marche.be/gestmail"
            target="_blank"
            rel="noopener noreferrer"
            icon="heroicon-o-arrow-top-right-on-square"
            icon-position="after"
        >
            https://citoyen.marche.be/gestmail
        </x-filament::link>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Quelques commandes utiles</x-slot>

        <div class="space-y-6">
            @foreach ($this->getCommands() as $command)
                <div class="space-y-1">
                    <code class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                        {{ $command['command'] }}
                    </code>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $command['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Webmail citoyen</x-slot>

        <x-filament::link
            href="https://citoyen.marche.be"
            target="_blank"
            rel="noopener noreferrer"
            icon="heroicon-o-arrow-top-right-on-square"
            icon-position="after"
        >
            Accéder au webmail citoyen
        </x-filament::link>
    </x-filament::section>
</x-filament-panels::page>
