<x-filament-panels::page>
    <x-filament::section>
        <div class="flex flex-wrap gap-1">
            <button type="button"
                    wire:click="selectLetter(null)"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md border
                        @if ($currentLetter === null) bg-primary-600 text-white border-primary-600
                        @else bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 @endif">
                Tout
            </button>

            @foreach (range('A', 'Z') as $letter)
                @php $hasResults = in_array($letter, $letters, true); @endphp
                <button type="button"
                        wire:click="selectLetter('{{ $letter }}')"
                        @disabled(! $hasResults)
                        class="px-2.5 py-1 text-xs font-semibold rounded-md border
                            @if ($currentLetter === $letter) bg-primary-600 text-white border-primary-600
                            @elseif ($hasResults) bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800
                            @else bg-gray-50 dark:bg-gray-900 text-gray-300 dark:text-gray-700 border-gray-200 dark:border-gray-800 cursor-not-allowed @endif">
                    {{ $letter }}
                </button>
            @endforeach
        </div>
    </x-filament::section>

    @if ($currentLetter !== null)
        <x-filament::section>
            <x-slot name="heading">
                {{ $currentLetter }} ({{ $employees->count() }})
            </x-slot>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($employees as $employee)
                    @include('who-is-who::filament.components.employee-card', ['employee' => $employee])
                @endforeach
            </div>
        </x-filament::section>
    @else
        @foreach ($grouped as $letter => $letterEmployees)
            <x-filament::section collapsible :collapsed="false">
                <x-slot name="heading">
                    {{ $letter }} ({{ $letterEmployees->count() }})
                </x-slot>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($letterEmployees as $employee)
                        @include('who-is-who::filament.components.employee-card', ['employee' => $employee])
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    @endif
</x-filament-panels::page>
