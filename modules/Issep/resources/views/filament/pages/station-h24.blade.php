<x-filament-panels::page>
    @if (filled($apiError))
        <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="danger">
            <x-slot:heading>Données indisponibles</x-slot:heading>

            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $apiError }}</p>
        </x-filament::section>
    @endif

    <x-filament::section>
        <x-slot:heading>Dernier relevé</x-slot:heading>

        @if ($lastIndice)
            <dl class="grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</dt>
                    <dd class="text-sm text-gray-950 dark:text-white">
                        {{ $lastIndice['ts']?->translatedFormat('l j F Y à H:i') ?? 'Inconnue' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Indice BelAQI</dt>
                    <dd>
                        <x-filament::badge :color="$lastIndice['color']">
                            {{ $lastIndice['label_with_value'] }}
                        </x-filament::badge>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Point de mesure</dt>
                    <dd class="text-sm text-gray-950 dark:text-white">
                        {{ filled($lastIndice['point_name']) ? $lastIndice['point_name'] : '—' }}
                    </dd>
                </div>
            </dl>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Pas de dernier relevé pour cette station.</p>
        @endif
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>
