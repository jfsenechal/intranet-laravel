<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ $employees->count() }} complice(s) du quotidien
        </x-slot>

        @if ($employees->isEmpty())
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Vous n'avez pas encore de collègue favori. Cliquez sur l'étoile d'une fiche dans l'annuaire pour l'ajouter ici.
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($employees as $employee)
                    @include('who-is-who::filament.components.employee-card', ['employee' => $employee])
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
