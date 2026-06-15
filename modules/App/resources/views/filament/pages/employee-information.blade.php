<x-filament-panels::page>
    @if ($employee)
        {{ $this->employeeInfolist }}
    @else
        <x-filament::section>
            Aucune fiche du personnel n'est associée à votre compte.
        </x-filament::section>
    @endif
</x-filament-panels::page>
