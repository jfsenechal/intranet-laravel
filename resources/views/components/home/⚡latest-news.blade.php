<?php

use AcMarche\News\Filament\Resources\News\NewsResource;
use AcMarche\News\Models\News;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, News>
     */
    public function news(): Collection
    {
        return News::query()->latest('created_at')->limit(6)->get();
    }

    public function with(): array
    {
        return ['latestNews' => $this->news()];
    }
};
?>

<div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up lg:col-span-2" style="--delay: 0.1s">
    <div class="gradient-news flex items-center justify-between p-5 text-white">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold">Dernières actualités</h2>
        </div>
        <span class="rounded-full bg-white/20 px-3 py-1  font-semibold backdrop-blur">
            {{ $latestNews->count() }}
        </span>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($latestNews as $index => $news)
            <a
                href="{{ NewsResource::getUrl('view', ['record' => $news->id], panel: 'news') }}"
                class="group flex items-start gap-3 p-4 transition hover:bg-gray-50 animate-fade-in-up"
                style="--delay: {{ 0.15 + ($index * 0.05) }}s"
            >
                <div class="mt-1 size-2 shrink-0 rounded-full bg-blue-500 group-hover:animate-pulse"></div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-gray-900 group-hover:text-blue-600">
                        {{ $news->title ?? $news->name }}
                    </p>
                    <p class="mt-0.5  text-gray-500">
                        {{ $news->created_at?->translatedFormat('d F Y') }}
                        @if ($news->user_add)
                            — {{ $news->user_add }}
                        @endif
                    </p>
                </div>
            </a>
        @empty
            <p class="p-6 text-center text-sm text-gray-500">Aucune actualité récente.</p>
        @endforelse
    </div>
</div>
