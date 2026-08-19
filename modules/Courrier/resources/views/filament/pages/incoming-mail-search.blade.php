<x-filament-panels::page>
    {{ $this->form }}

    @if (filled($this->executedQuery))
        <p class="-mb-4 font-mono text-xs break-all text-gray-400 dark:text-gray-500">
            {{ $this->executedQuery }}
        </p>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
