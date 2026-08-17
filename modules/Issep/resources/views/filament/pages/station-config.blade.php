<x-filament-panels::page>
    @if (filled($apiError))
        <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="danger">
            <x-slot:heading>Données indisponibles</x-slot:heading>

            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $apiError }}</p>
        </x-filament::section>
    @endif

    @if ($station)
        <x-filament::section collapsible>
            <x-slot:heading>La station</x-slot:heading>

            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'Numéro' => $station->id,
                    'Identifiant réseau' => $station->idReseau,
                    'Identifiant de la configuration' => $station->idConfiguration,
                    'Altitude' => $station->altitude,
                    'Hauteur' => $station->h,
                    'X / Y' => filled($station->x) ? $station->x.' / '.$station->y : null,
                    'Début d’attribution' => $station->attribStart?->translatedFormat('j F Y'),
                    'Fin d’attribution' => $station->attribEnd?->translatedFormat('j F Y'),
                ] as $label => $value)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="text-sm text-gray-950 dark:text-white">
                            {{ filled($value) ? $value : '—' }}
                        </dd>
                    </div>
                @endforeach

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Latitude / longitude</dt>
                    <dd class="text-sm text-gray-950 dark:text-white">
                        @if ($station->openStreetMapUrl())
                            <a
                                href="{{ $station->openStreetMapUrl() }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary-600 hover:underline dark:text-primary-400"
                            >
                                {{ $station->lat }}, {{ $station->lon }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </x-filament::section>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
