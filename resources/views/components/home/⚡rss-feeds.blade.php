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
     * @var list<array{title: string, url: string}>
     */
    public const array RSS_FEEDS = [
        ['title' => 'Le Soir', 'url' => 'https://www.lesoir.be/rss/31874/cible_principale'],
        ['title' => 'L\'Avenir Luxembourg', 'url' => 'https://www.lavenir.net/rss.aspx?foto=1&intro=1&section=zipcode&zipcode=6900'],
        ['title' => 'UVCW', 'url' => 'https://www.uvcw.be/rss/fil-rss.xml'],
        ['title' => 'DH Luxembourg', 'url' => 'https://www.dhnet.be/rss/section/regions/luxembourg.xml'],
    ];

    /**
     * @return array<int, array{title: string, link: string, source: string, date: ?string}>
     */
    public function items(): array
    {
        return Cache::remember('homepage.rss.items', now()->addMinutes(30), function (): array {
            $items = [];

            foreach (self::RSS_FEEDS as $feed) {
                try {
                    $response = Http::timeout(5)->get($feed['url']);

                    if (! $response->successful()) {
                        continue;
                    }

                    $xml = @simplexml_load_string($response->body());

                    if ($xml === false) {
                        continue;
                    }

                    $entries = $xml->channel->item ?? $xml->entry ?? [];

                    foreach ($entries as $index => $entry) {
                        if ($index >= 5) {
                            break;
                        }

                        $items[] = [
                            'title' => mb_trim((string) ($entry->title ?? '')),
                            'link' => mb_trim((string) ($entry->link ?? '')),
                            'source' => $feed['title'],
                            'date' => (string) ($entry->pubDate ?? $entry->published ?? '') ?: null,
                        ];
                    }
                } catch (\Throwable) {
                    continue;
                }
            }

            return $items;
        });
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200" style="--delay: 0.45s">
            <div class="gradient-rss flex items-center gap-3 p-5 text-white">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur"></div>
                <h2 class="text-lg font-bold">Flux d'actualité</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="h-16 animate-pulse rounded bg-gray-200"></div>
                <div class="h-16 animate-pulse rounded bg-gray-200"></div>
                <div class="h-16 animate-pulse rounded bg-gray-200"></div>
            </div>
        </div>
        HTML;
    }

    public function with(): array
    {
        return ['rssItems' => $this->items()];
    }
};
?>

<div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.45s">
    <div class="gradient-rss flex items-center justify-between p-5 text-white">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5.5 18a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM.5 10.5v3.5a9 9 0 019 9h3.5A12.5 12.5 0 00.5 10.5zm0-7v3.5A16 16 0 0116.5 23H20A19.5 19.5 0 00.5 3.5z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold">Flux d'actualité</h2>
        </div>
        <span class="rounded-full bg-white/20 px-3 py-1  font-semibold backdrop-blur">
            {{ count($rssItems) }}
        </span>
    </div>
    <div class="grid grid-cols-1 divide-y divide-gray-100 md:grid-cols-2 md:divide-y-0 md:divide-x lg:grid-cols-3">
        @forelse ($rssItems as $index => $item)
            <a
                href="{{ $item['link'] }}"
                target="_blank"
                rel="noopener noreferrer"
                class="group block p-4 transition hover:bg-gray-50 animate-fade-in-up"
                style="--delay: {{ 0.5 + ($index * 0.03) }}s"
            >
                <p class="mb-1  font-semibold uppercase tracking-wide text-purple-600">
                    {{ $item['source'] }}
                </p>
                <p class="line-clamp-2 text-sm font-medium text-gray-900 group-hover:text-purple-700">
                    {{ $item['title'] }}
                </p>
                @if ($item['date'])
                    <p class="mt-1  text-gray-500">
                        {{ Carbon::parse($item['date'])->translatedFormat('d F Y à H:i') }}
                    </p>
                @endif
            </a>
        @empty
            <p class="col-span-full p-6 text-center text-sm text-gray-500">Aucun flux disponible.</p>
        @endforelse
    </div>
</div>
