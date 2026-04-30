<?php

use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, Employee>
     */
    public function employees(): Collection
    {
        return Employee::query()
            ->where('is_archived', false)
            ->whereNotNull('hired_at')
            ->whereHas('activeContracts')
            ->with('activeContracts')
            ->orderByDesc('hired_at')
            ->limit(5)
            ->get();
    }

    public function with(): array
    {
        return ['latestEmployees' => $this->employees()];
    }
};
?>

<div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.3s">
    <div class="gradient-employee flex items-center justify-between p-4 text-white">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h2 class="text-base font-bold">Nouveaux agents</h2>
        </div>
        <span class="rounded-full bg-white/20 px-2 py-0.5  font-semibold backdrop-blur">
            {{ $latestEmployees->count() }}
        </span>
    </div>
    <div class="p-4">
        @forelse ($latestEmployees as $index => $employee)
            <a
                href="{{ EmployeeResource::getUrl('view', ['record' => $employee->id], panel: 'hrm-panel') }}"
                class="group flex items-center gap-3 border-b border-gray-100 py-2 last:border-0 animate-fade-in-up"
                style="--delay: {{ 0.35 + ($index * 0.05) }}s"
            >
                @if ($employee->photo && Storage::disk('public')->exists($employee->photo))
                    <img
                        src="{{ Storage::disk('public')->url($employee->photo) }}"
                        alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                        class="h-10 w-10 rounded-full object-cover ring-2 ring-indigo-300"
                    />
                @else
                    <img
                        src="https://ui-avatars.com/api/?size=128&background=6366f1&color=fff&name={{ urlencode(trim($employee->first_name.' '.$employee->last_name)) }}"
                        alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                        class="h-10 w-10 rounded-full object-cover ring-2 ring-indigo-300"
                    />
                @endif
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-900 group-hover:text-indigo-600">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </p>
                    @if ($employee->hired_at)
                        <p class=" text-gray-500">
                            engagé le {{ $employee->hired_at->translatedFormat('d F Y') }}
                        </p>
                    @endif
                </div>
            </a>
        @empty
            <p class="text-center  text-gray-500">Aucun nouvel agent.</p>
        @endforelse
    </div>
</div>
