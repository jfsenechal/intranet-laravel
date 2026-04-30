<x-filament-panels::page>
    @if ($error)
        <x-filament::section>
            <div class="text-danger-600 dark:text-danger-400">
                {{ $error }}
            </div>
        </x-filament::section>
    @endif

    <x-filament::section>
        <x-slot name="heading">
            Derniers SMS envoyés via l'API Inforius
        </x-slot>
        <x-slot name="description">
            Données récupérées en direct depuis l'API (2 derniers mois).
        </x-slot>

        @if (empty($lines))
            <div class="text-gray-500 dark:text-gray-400 py-6 text-center">
                Aucun SMS dans l'historique.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="py-2 pr-4">Date</th>
                            <th class="py-2 pr-4">Destinataire</th>
                            <th class="py-2 pr-4">Statut</th>
                            <th class="py-2 pr-4">Référence</th>
                            <th class="py-2 pr-4">Coût réel</th>
                            <th class="py-2 pr-4">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($lines as $line)
                            <tr class="align-top">
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $line->date }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $line->recipient }}</td>
                                <td class="py-2 pr-4">{{ $line->statusText }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $line->customerReference }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ number_format($line->realCost, 4, ',', ' ') }}</td>
                                <td class="py-2 pr-4">{{ $line->content }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
