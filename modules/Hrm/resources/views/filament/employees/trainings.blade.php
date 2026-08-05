@php
    /** @var array<int, array{type: \AcMarche\Hrm\Enums\TrainingTypeEnum, trainings: \Illuminate\Support\Collection<int, \AcMarche\Hrm\Models\Training>, urls: array<int, string|null>, total: int}> $groups */
@endphp

<div class="space-y-6">
    @forelse ($groups as $group)
        @php
            $type = $group['type'];
        @endphp

        <section class="fi-section overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <header class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-base font-semibold text-pink-500 dark:text-white">
                        Formation {{ $type->getLabel() }}
                    </h3>
                    <span class="fi-badge inline-flex items-center rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                        {{ $group['trainings']->count() }} formation(s) · {{ \AcMarche\Hrm\Models\Training::formatDuration($group['total']) ?: '0min' }}
                    </span>
                </div>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                    {{ $type->getDescription() }}
                </p>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full table-auto divide-y divide-gray-200 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Intitulé
                            </th>
                            <th scope="col" class="whitespace-nowrap px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Date
                            </th>
                            <th scope="col" class="whitespace-nowrap px-6 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Durée
                            </th>
                            <th scope="col" class="whitespace-nowrap px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Attestation
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($group['trainings'] as $training)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-6 py-3 font-medium text-gray-950 dark:text-white">
                                    @php
                                        $url = $group['urls'][$training->id] ?? null;
                                    @endphp

                                    @if ($url)
                                        <a
                                            href="{{ $url }}"
                                            class="fi-link text-primary-600 hover:underline dark:text-primary-400"
                                        >
                                            {{ $training->name }}
                                        </a>
                                    @else
                                        {{ $training->name }}
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-3 text-gray-600 dark:text-gray-300">
                                    @if ($training->start_date)
                                        {{ $training->start_date->format('d/m/Y') }}
                                        @if ($training->end_date && ! $training->end_date->isSameDay($training->start_date))
                                            <span class="text-gray-400 dark:text-gray-500">&rarr;</span>
                                            {{ $training->end_date->format('d/m/Y') }}
                                        @endif
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-3 text-end tabular-nums text-gray-600 dark:text-gray-300">
                                    {{ \AcMarche\Hrm\Models\Training::formatDuration($training->duration_minutes) ?: '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-3 text-center">
                                    @if ($training->certificate_received)
                                        <x-filament::icon
                                            icon="heroicon-o-check-circle"
                                            class="mx-auto h-5 w-5 text-success-600 dark:text-success-400"
                                        />
                                        <span class="sr-only">Attestation reçue</span>
                                    @else
                                        <x-filament::icon
                                            icon="heroicon-o-x-circle"
                                            class="mx-auto h-5 w-5 text-danger-600 dark:text-danger-400"
                                        />
                                        <span class="sr-only">Attestation non reçue</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="border-t border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
                        <tr>
                            <td colspan="2" class="px-6 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Total
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-end font-semibold tabular-nums text-gray-950 dark:text-white">
                                {{ \AcMarche\Hrm\Models\Training::formatDuration($group['total']) ?: '0min' }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Aucune formation encodée pour cet agent.
        </p>
    @endforelse
</div>
