<?php

use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use AcMarche\Ad\Models\ClassifiedAd;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, ClassifiedAd>
     */
    public function ads(): Collection
    {
        return ClassifiedAd::query()->latest('created_at')->limit(4)->get();
    }

    public function with(): array
    {
        return ['latestAds' => $this->ads()];
    }
};
?>

<div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.25s">
    <div class="gradient-classified flex items-center justify-between p-4 text-white">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <h2 class="text-base font-bold">Petites annonces</h2>
        </div>
        <span class="rounded-full bg-white/20 px-2 py-0.5  font-semibold backdrop-blur">
            {{ $latestAds->count() }}
        </span>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($latestAds as $index => $ad)
            <a
                href="{{ ClassifiedAdResource::getUrl('view', ['record' => $ad->id], panel: 'ad') }}"
                class="group block p-3 transition hover:bg-gray-50 animate-fade-in-up"
                style="--delay: {{ 0.3 + ($index * 0.05) }}s"
            >
                <p class="truncate text-sm font-medium text-gray-900 group-hover:text-pink-600">
                    {{ $ad->title ?? $ad->name }}
                </p>
                <p class="mt-0.5  text-gray-500">
                    {{ $ad->created_at?->translatedFormat('d F Y') }}
                </p>
            </a>
        @empty
            <p class="p-4 text-center  text-gray-500">Aucune annonce.</p>
        @endforelse
    </div>
</div>
