@php
    /** @var \Illuminate\Support\Collection<int, \AcMarche\Hrm\Models\Employee> $matches */
    /** @var string $lastName */
    /** @var string $firstName */
@endphp

<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-content-ctn p-6">
        @if (mb_strlen($lastName) < 2)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Saisissez au moins 2 lettres du nom pour vérifier les doublons.
            </p>
        @elseif ($matches->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Aucun agent existant ne correspond à ce nom.
            </p>
        @else
            <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ $matches->count() }} agent(s) existant(s) — cliquez pour ouvrir, sinon poursuivez la création.
            </p>
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($matches as $match)
                    <li class="py-2">
                        <a
                            href="{{ \AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource::getUrl('view', ['record' => $match]) }}"
                            class="fi-link text-sm font-medium text-primary-600 hover:underline dark:text-primary-400"
                        >
                            {{ $match->last_name }} {{ $match->first_name }}
                        </a>
                        @if ($match->birth_date)
                            <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                — né(e) le {{ $match->birth_date->format('d/m/Y') }}
                            </span>
                        @endif
                        @if ($match->job_title)
                            <span class="ml-2 text-xs text-gray-400 dark:text-gray-500">
                                · {{ $match->job_title }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
