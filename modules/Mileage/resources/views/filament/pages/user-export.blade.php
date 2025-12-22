<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Recherche par utilisateur
        </x-slot>
        <x-slot name="description">
            Sélectionnez un utilisateur pour afficher le récapitulatif de ses déclarations par année et par mois.
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
                Récapitulatif pour {{ $selectedUsername }}
            </x-slot>

            @if($declaration)
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h3 class="font-semibold text-lg mb-2 text-yellow-800">
                        {{ strtoupper($declaration->last_name) }} {{ $declaration->first_name }}
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p>{{ $declaration->street }}</p>
                            <p>{{ $declaration->postal_code }} {{ $declaration->city }}</p>
                        </div>
                        <div>
                            <p><strong>IBAN :</strong> {{ $declaration->iban }}</p>
                            <p><strong>Omnium :</strong> {{ $declaration->omnium ? 'Oui' : 'Non' }}</p>
                            @if($declaration->college_date)
                                <p><strong>Délibé Collège :</strong> {{ $declaration->college_date->format('d-m-Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left">Année</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left">Type</th>
                        @foreach($months as $month)
                            <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">{{ $month }}</th>
                        @endforeach
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $internes = $trips['interne'];
                        $externes = $trips['externe'];
                    @endphp

                    @foreach($years as $year)
                        @php
                            $totalInterne = 0;
                            $totalExterne = 0;
                            $hasInterneData = isset($internes[$year]) && count($internes[$year]) > 0;
                            $hasExterneData = isset($externes[$year]) && count($externes[$year]) > 0;
                        @endphp

                        @if($hasInterneData || $hasExterneData)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 font-semibold" rowspan="2">{{ $year }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">Interne</td>
                                @foreach(array_keys($months) as $nummonth)
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        @if(isset($internes[$year][$nummonth]))
                                            {{ $internes[$year][$nummonth] }}
                                            @php $totalInterne += $internes[$year][$nummonth]; @endphp
                                        @endif
                                    </td>
                                @endforeach
                                <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-semibold">{{ $totalInterne }} km</td>
                            </tr>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">Externe</td>
                                @foreach(array_keys($months) as $nummonth)
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        @if(isset($externes[$year][$nummonth]))
                                            {{ $externes[$year][$nummonth] }}
                                            @php $totalExterne += $externes[$year][$nummonth]; @endphp
                                        @endif
                                    </td>
                                @endforeach
                                <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right font-semibold">{{ $totalExterne }} km</td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>

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
        </x-filament::section>
    @endif
</x-filament-panels::page>
