<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use AcMarche\Ad\Models\ClassifiedAd;
use AcMarche\Document\Models\Document;
use AcMarche\Hrm\Models\Employee;
use AcMarche\News\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class HomeController extends Controller
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

    public function __invoke(): View
    {
        return view('home', [
            'latestNews' => News::query()->latest('created_at')->limit(6)->get(),
            'latestDocuments' => Document::query()->latest('created_at')->limit(6)->get(),
            'latestAds' => ClassifiedAd::query()->latest('created_at')->limit(4)->get(),
            'latestEmployees' => $this->fetchLatestEmployees(),
            'todayBirthdays' => $this->fetchTodayBirthdays(),
            'rssItems' => $this->fetchRssItems(),
            'pressArticles' => $this->fetchPressArticles(),
        ]);
    }

    /**
     * @return Collection<int, Employee>
     */
    private function fetchLatestEmployees(): Collection
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

    /**
     * @return Collection<int, Employee>
     */
    private function fetchTodayBirthdays(): Collection
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

    /**
     * @return array<int, array{title: string, link: string, source: string, date: ?string}>
     */
    private function fetchRssItems(): array
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
                } catch (Throwable) {
                    continue;
                }
            }

            return $items;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchPressArticles(): array
    {
        return Cache::remember('homepage.press.articles', now()->addMinutes(30), function (): array {
            try {
                $response = Http::timeout(5)->get('https://presse.marche.be/api/articles');

                if (! $response->successful()) {
                    return [];
                }

                $data = $response->json();

                return is_array($data) ? array_slice($data, 0, 6) : [];
            } catch (Throwable) {
                return [];
            }
        });
    }
}
