<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * A minimal RSS 2.0 channel holding the given number of items.
 */
function rssChannel(int $itemCount, string $prefix = 'Article'): string
{
    $items = '';

    for ($i = 1; $i <= $itemCount; $i++) {
        $items .= <<<XML
            <item>
                <title>{$prefix} {$i}</title>
                <link>https://example.test/{$prefix}-{$i}</link>
                <pubDate>Wed, 05 Aug 2026 15:21:00 +0100</pubDate>
            </item>
            XML;
    }

    return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0"><channel><title>Feed</title>{$items}</channel></rss>
        XML;
}

/**
 * The lazy placeholder would hide the real output, so always render eagerly.
 */
function rssFeeds(): \Livewire\Features\SupportTesting\Testable
{
    return Livewire::withoutLazyLoading()->test('home.rss-feeds');
}

beforeEach(function (): void {
    Cache::forget('homepage.rss.items');
});

it('renders entries from every reachable feed', function (): void {
    Http::fake(['*' => Http::response(rssChannel(3))]);

    rssFeeds()
        ->assertOk()
        ->assertSee('Article 1')
        ->assertSee('Article 3')
        ->assertDontSee('Aucun flux disponible.');
});

it('keeps at most five entries per feed', function (): void {
    Http::fake(['*' => Http::response(rssChannel(9))]);

    $feedCount = count(Livewire::new('home.rss-feeds')::RSS_FEEDS);

    $items = rssFeeds()->viewData('rssItems');

    expect($items)->toHaveCount(5 * $feedCount)
        ->and(collect($items)->pluck('title')->unique()->all())
        ->toBe(['Article 1', 'Article 2', 'Article 3', 'Article 4', 'Article 5']);
});

it('sends a user agent so publishers do not reject the request', function (): void {
    Http::fake(['*' => Http::response(rssChannel(1))]);

    rssFeeds()->assertOk();

    Http::assertSent(fn ($request): bool => str_contains(
        (string) $request->header('User-Agent')[0],
        'Mozilla/5.0',
    ));
});

it('skips feeds that fail without dropping the others', function (): void {
    Http::fake([
        'www.lesoir.be/*' => Http::response('Access Denied', 403),
        'www.uvcw.be/*' => Http::response('not xml at all', 200),
        '*' => Http::response(rssChannel(2, 'Ok')),
    ]);

    rssFeeds()
        ->assertOk()
        ->assertSee('Ok 1')
        ->assertDontSee('Aucun flux disponible.');
});

it('repairs entities the uvcw feed mangles into " et xxx;"', function (string $raw, string $expected): void {
    expect(Livewire::new('home.rss-feeds')->repairMangledEntities($raw))->toBe($expected);
})->with([
    // SimpleXML has already decoded real entities such as &#233; by the time this runs.
    'apostrophe' => ['Urgence en Espagne : l et apos;armée au secours', "Urgence en Espagne : l'armée au secours"],
    'ampersand' => ['Citoyenneté  et amp; Participation', 'Citoyenneté & Participation'],
    'non breaking space' => ['Perturbations au SPW et nbsp;: consignes', "Perturbations au SPW\u{00A0}: consignes"],
    'numeric entity' => ['Caf et #233; communal', 'Café communal'],
    'several in one title' => [
        'Formation : l et apos;Union a remis son avis au Ministre de l et apos;Intérieur',
        "Formation : l'Union a remis son avis au Ministre de l'Intérieur",
    ],
    'plain french text is untouched' => ['Les communes et provinces; suite', 'Les communes et provinces; suite'],
    'unknown entity is untouched' => ['Budget et cetera; 2026', 'Budget et cetera; 2026'],
    'trims surrounding whitespace' => ['  Conseil communal  ', 'Conseil communal'],
]);

it('decodes mangled entities in the rendered feed', function (): void {
    $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0"><channel><title>Feed</title>
            <item>
                <title>Redevances : l et apos;Union maintient sa position</title>
                <link>https://example.test/uvcw</link>
                <pubDate>Wed, 05 Aug 2026 15:21:00 +0100</pubDate>
            </item>
        </channel></rss>
        XML;

    Http::fake(['*' => Http::response($xml)]);

    rssFeeds()
        ->assertOk()
        ->assertSee("l'Union maintient sa position")
        ->assertDontSee('et apos;');
});

it('tells the user when no feed could be read', function (): void {
    Http::fake(['*' => Http::response('', 500)]);

    rssFeeds()
        ->assertOk()
        ->assertSee('Aucun flux disponible.');
});
