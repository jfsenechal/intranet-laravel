<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="search" class="space-y-4">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                    Rechercher
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if ($searched)
        <x-filament::section>
            <x-slot name="heading">
                @if ($results->isEmpty())
                    Aucun résultat
                @else
                    {{ $results->count() }} résultat(s) pour « {{ $term }} »
                @endif
            </x-slot>

            @if ($results->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-4">
                    @foreach ($results as $employee)
                        @include('who-is-who::filament.components.employee-card', ['employee' => $employee])
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
