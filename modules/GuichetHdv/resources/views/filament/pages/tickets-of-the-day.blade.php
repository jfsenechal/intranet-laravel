<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- En attente --}}
        <x-filament::section icon="heroicon-o-clock" icon-color="warning">
            <x-slot name="heading">En attente</x-slot>
            <x-slot name="description">Tickets sans guichet assigné</x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="warning">{{ $pending->count() }}</x-filament::badge>
            </x-slot>

            @forelse ($pending as $ticket)
                <div @class([
                    'flex items-start justify-between gap-3 py-3 text-sm',
                    'border-t border-gray-100 dark:border-white/10' => ! $loop->first,
                ])>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold tabular-nums">#{{ $ticket->number }}</span>
                            <x-filament::badge color="gray" size="sm">{{ $ticket->service }}</x-filament::badge>
                        </div>
                        <p class="mt-1 truncate text-gray-500 dark:text-gray-400">{{ $ticket->reason }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <span class="tabular-nums text-gray-400">{{ $ticket->createdAt?->format('H:i') }}</span>
                        <div class="flex items-center gap-1">
                            {{ ($this->assignOfficeAction)(['ticket' => $ticket->id]) }}
                            {{ ($this->cancelTicketAction)(['ticket' => $ticket->id]) }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-400">Aucun ticket en attente.</p>
            @endforelse
        </x-filament::section>

        {{-- En traitement --}}
        <x-filament::section icon="heroicon-o-arrow-path" icon-color="info">
            <x-slot name="heading">En traitement</x-slot>
            <x-slot name="description">Tickets pris en charge à un guichet</x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="info">{{ $processing->count() }}</x-filament::badge>
            </x-slot>

            @forelse ($processing as $ticket)
                <div @class([
                    'flex items-start justify-between gap-3 py-3 text-sm',
                    'border-t border-gray-100 dark:border-white/10' => ! $loop->first,
                ])>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold tabular-nums">#{{ $ticket->number }}</span>
                            <x-filament::badge color="gray" size="sm">{{ $ticket->service }}</x-filament::badge>
                        </div>
                        <p class="mt-1 truncate text-gray-500 dark:text-gray-400">{{ $ticket->reason }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2 text-right">
                        <x-filament::badge color="info" size="sm">{{ $ticket->office?->name ?? '—' }}</x-filament::badge>
                        <span class="tabular-nums text-gray-400">{{ $ticket->createdAt?->format('H:i') }}</span>
                        {{ ($this->cancelTicketAction)(['ticket' => $ticket->id]) }}
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-400">Aucun ticket en traitement.</p>
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
