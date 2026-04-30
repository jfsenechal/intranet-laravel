<?php

use AcMarche\Hrm\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, Employee>
     */
    public function birthdays(): Collection
    {
        $today = Carbon::today();

        return Employee::query()
            ->where('show_birthday', true)
            ->whereNotNull('birth_date')
            ->whereRaw('MONTH(birth_date) = ?', [$today->month])
            ->whereRaw('DAY(birth_date) = ?', [$today->day])
            ->whereHas('activeContracts')
            ->with('activeContracts')
            ->orderBy('last_name')
            ->get();
    }

    public function with(): array
    {
        return [
            'todayBirthdays' => $this->birthdays(),
            'today' => Carbon::today()->translatedFormat('d F'),
        ];
    }
};
?>

<div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.15s">
    <div class="gradient-birthday flex items-center gap-3 p-4 text-white">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A2.704 2.704 0 003 15.546V21h18v-5.454zM12 3v2m0 2a2 2 0 100-4 2 2 0 000 4zm0 0v6m-3 4h6" />
            </svg>
        </div>
        <div class="min-w-0">
            <h2 class="text-base font-bold">Anniversaires</h2>
            <p class="text-xs opacity-90">{{ $today }}</p>
        </div>
    </div>
    <div class="p-4">
        @forelse ($todayBirthdays as $index => $employee)
            <div class="flex items-center gap-3 border-b border-gray-100 py-2 last:border-0 animate-fade-in-up" style="--delay: {{ 0.2 + ($index * 0.05) }}s">
                <div class="relative">
                    @if ($employee->photo && Storage::disk('public')->exists($employee->photo))
                        <img
                            src="{{ Storage::disk('public')->url($employee->photo) }}"
                            alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                            class="h-10 w-10 rounded-full object-cover ring-2 ring-amber-400"
                        />
                    @else
                        <img
                            src="https://ui-avatars.com/api/?size=128&background=fbbf24&color=fff&name={{ urlencode(trim($employee->first_name.' '.$employee->last_name)) }}"
                            alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                            class="h-10 w-10 rounded-full object-cover ring-2 ring-amber-400"
                        />
                    @endif
                    <span class="absolute -right-1 -top-1 text-sm animate-float">🎂</span>
                </div>
                <p class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900">
                    {{ $employee->first_name }} {{ $employee->last_name }}
                </p>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-4 text-center">
                <span class="text-2xl">🎈</span>
                <p class="mt-1 text-xs text-gray-500">Aucun anniversaire aujourd'hui.</p>
            </div>
        @endforelse
    </div>
</div>
