<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Ce que liste cette page
        </x-slot>
        <x-slot name="description">
            Un déplacement déclaré est remboursé au tarif figé sur sa déclaration, multiplié par la distance.
            Ces déclarations portent un tarif qui n'est pas celui du barème officiel des dates de leurs
            déplacements : leur bénéficiaire a donc été sous-payé ou sur-payé. Corrigez le tarif sur la
            déclaration elle-même.
        </x-slot>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                <div class="text-2xl font-semibold tabular-nums">{{ $totalDeclarations }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Déclarations</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                <div class="text-2xl font-semibold tabular-nums">{{ $totalTrips }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Déplacements concernés</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                <div @class([
                    'text-2xl font-semibold tabular-nums',
                    'text-danger-600 dark:text-danger-400' => $totalDelta > 0,
                    'text-success-600 dark:text-success-400' => $totalDelta < 0,
                ])>
                    {{ $totalDelta > 0 ? '+' : '' }}{{ number_format($totalDelta, 2, ',', ' ') }} €
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Écart net (dû aux agents)</div>
            </div>
        </div>

        @if ($storageInconsistencies > 0)
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Par ailleurs, {{ $storageInconsistencies }} déplacement(s) portent un tarif qui diverge de
                leur propre déclaration. Le remboursement n'en dépend pas, donc rien à corriger ici :
                <code class="text-xs">php artisan mileage:verify-trip-rates --fix</code> les réaligne.
            </p>
        @endif
    </x-filament::section>

    <x-filament::section>
        <div class="flex flex-wrap items-end gap-3">
            <div class="grow" style="min-width: 14rem">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher un nom, un identifiant, un n° de déclaration…"
                    />
                </x-filament::input.wrapper>
            </div>

            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="sort">
                    <option value="delta">Trier par écart</option>
                    <option value="trips">Trier par nombre de déplacements</option>
                    <option value="name">Trier par nom</option>
                    <option value="id">Trier par n° de déclaration</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="fromYear">
                    @foreach ($this->getYearOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <div class="mt-4 space-y-2">
            @forelse ($declarations as $declaration)
                <details class="rounded-lg border border-gray-200 dark:border-gray-700">
                    <summary class="flex cursor-pointer flex-wrap items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-900">
                        <x-filament::badge>#{{ $declaration['id'] }}</x-filament::badge>

                        <span class="grow">
                            <span class="font-medium">
                                {{ $declaration['last_name'] }} {{ $declaration['first_name'] }}
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                {{ $declaration['user_add'] }}
                                @if ($declaration['declared_at'])
                                    · déclarée le
                                    {{ \Illuminate\Support\Carbon::parse($declaration['declared_at'])->format('d/m/Y') }}
                                @endif
                                · payée à {{ rtrim(rtrim(number_format($declaration['paid'], 4, '.', ''), '0'), '.') }}
                            </span>
                        </span>

                        <x-filament::badge color="gray">
                            {{ $declaration['trip_count'] }} dépl.
                        </x-filament::badge>

                        <x-filament::badge :color="$declaration['delta'] >= 0 ? 'danger' : 'success'">
                            {{ $declaration['delta'] >= 0 ? '+' : '' }}{{ number_format($declaration['delta'], 2, ',', ' ') }} €
                        </x-filament::badge>
                    </summary>

                    <div class="border-t border-gray-200 p-3 dark:border-gray-700">
                        <x-filament::link
                            :href="\AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource::getUrl('view', ['record' => $declaration['id']])"
                            size="sm"
                        >
                            Ouvrir la déclaration
                        </x-filament::link>

                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                                        <th class="py-1 pr-4 font-medium">Date</th>
                                        <th class="py-1 pr-4 text-right font-medium">Distance</th>
                                        <th class="py-1 pr-4 text-right font-medium">Payé → barème</th>
                                        <th class="py-1 text-right font-medium">Écart</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($declaration['trips'] as $trip)
                                        <tr class="border-t border-gray-100 dark:border-gray-800">
                                            <td class="py-1 pr-4">
                                                {{ \Illuminate\Support\Carbon::parse($trip['date'])->format('d/m/Y') }}
                                            </td>
                                            <td class="py-1 pr-4 text-right tabular-nums">{{ $trip['distance'] }} km</td>
                                            <td class="py-1 pr-4 text-right tabular-nums">
                                                <span class="text-gray-400 line-through">
                                                    {{ rtrim(rtrim(number_format($trip['paid'], 4, '.', ''), '0'), '.') }}
                                                </span>
                                                →
                                                <span class="font-medium">
                                                    {{ rtrim(rtrim(number_format($trip['official'], 4, '.', ''), '0'), '.') }}
                                                </span>
                                            </td>
                                            <td class="py-1 text-right tabular-nums">
                                                {{ $trip['delta'] >= 0 ? '+' : '' }}{{ number_format($trip['delta'], 2, ',', ' ') }} €
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            @empty
                <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Aucune déclaration ne correspond à ce filtre.
                </p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-panels::page>
