<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Lazy;
use Livewire\Component;

new
#[Lazy]
class extends Component
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function articles(): array
    {
        return Cache::remember('homepage.press.articles', now()->addMinutes(30), function (): array {
            try {
                $response = Http::timeout(5)->get('https://presse.marche.be/api/articles');

                if (! $response->successful()) {
                    return [];
                }

                $data = $response->json();

                return is_array($data) ? array_slice($data, 0, 6) : [];
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200" style="--delay: 0.4s">
            <div class="gradient-press flex items-center gap-3 p-5 text-white">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur"></div>
                <h2 class="text-lg font-bold">Revue de presse</h2>
            </div>
            <div class="space-y-3 p-4">
                <div class="h-4 w-3/4 animate-pulse rounded bg-gray-200"></div>
                <div class="h-4 w-1/2 animate-pulse rounded bg-gray-200"></div>
                <div class="h-4 w-2/3 animate-pulse rounded bg-gray-200"></div>
            </div>
        </div>
        HTML;
    }

    public function with(): array
    {
        return ['pressArticles' => $this->articles()];
    }
};
?>

<div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.4s">
    <div class="gradient-press flex items-center gap-3 p-5 text-white">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h2 class="text-lg font-bold">Revue de presse</h2>
    </div>
    <div class="max-h-[500px] divide-y divide-gray-100 overflow-y-auto">
        @forelse ($pressArticles as $index => $article)
            <a
                href="{{ $article['url'] ?? $article['link'] ?? '#' }}"
                target="_blank"
                rel="noopener noreferrer"
                class="group block p-4 transition hover:bg-gray-50 animate-fade-in-up"
                style="--delay: {{ 0.45 + ($index * 0.03) }}s"
            >
                <p class="line-clamp-2 text-sm font-semibold text-gray-900 group-hover:text-cyan-600">
                    {{ $article['title'] ?? $article['name'] ?? 'Article' }}
                </p>
                @if (! empty($article['source']) || ! empty($article['publisher']))
                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-cyan-600">
                        {{ $article['source'] ?? $article['publisher'] }}
                    </p>
                @endif
                @if (! empty($article['publishedAt']) || ! empty($article['published_at']) || ! empty($article['date']))
                    <p class="mt-1 text-xs text-gray-500">
                        {{ Carbon::parse($article['publishedAt'] ?? $article['published_at'] ?? $article['date'])->translatedFormat('d F Y') }}
                    </p>
                @endif
            </a>
        @empty
            <p class="p-6 text-center text-sm text-gray-500">Aucun article de presse disponible.</p>
        @endforelse
    </div>
</div>
