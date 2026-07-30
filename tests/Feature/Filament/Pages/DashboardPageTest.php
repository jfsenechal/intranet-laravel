<?php

declare(strict_types=1);

use AcMarche\App\Filament\Pages\DashboardPage;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Search\MeiliSearcher;
use AcMarche\Document\Models\Document;
use AcMarche\News\Models\News;
use App\Models\User;
use Filament\Facades\Filament;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Search\SearchResult;

use function Pest\Livewire\livewire;

/**
 * Bind a MeiliSearcher whose index returns the given mail ids, and capture the
 * search options (filter, sort, …) it was called with for assertion.
 *
 * @param  array<int, int>  $ids
 */
function fakeCourrierSearch(array $ids = []): stdClass
{
    $captured = new stdClass();
    $captured->options = [];

    $result = Mockery::mock(SearchResult::class);
    $result->shouldReceive('getHits')->andReturn(
        array_map(static fn (int $id): array => ['id' => $id], $ids),
    );

    $index = Mockery::mock(Indexes::class);
    $index->shouldReceive('search')
        ->andReturnUsing(function (string $query, array $options) use ($captured, $result): SearchResult {
            $captured->options = $options;

            return $result;
        });

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('index')->andReturn($index);

    config()->set('app.meilisearch.master_key', 'test-master-key');

    app()->bind(MeiliSearcher::class, function () use ($client): MeiliSearcher {
        $searcher = new MeiliSearcher();
        $searcher->client = $client;

        return $searcher;
    });

    return $captured;
}

/**
 * Bind a MeiliSearcher whose index throws, as during a Meilisearch outage.
 */
function failingCourrierSearch(): void
{
    $index = Mockery::mock(Indexes::class);
    $index->shouldReceive('search')->andThrow(new RuntimeException('Index `indicateur` not found.'));

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('index')->andReturn($index);

    config()->set('app.meilisearch.master_key', 'test-master-key');

    app()->bind(MeiliSearcher::class, function () use ($client): MeiliSearcher {
        $searcher = new MeiliSearcher();
        $searcher->client = $client;

        return $searcher;
    });
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
    fakeCourrierSearch();
});

it('lists recent mail the user is a recipient of', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $mine = IncomingMail::factory()->create();
    $mine->recipients()->attach($recipient->id);

    $captured = fakeCourrierSearch([$mine->id]);

    livewire(DashboardPage::class)
        ->assertSee($mine->reference_number)
        ->assertSeeHtml(route('filament.courrier-panel.resources.incoming-mails.view', ['record' => $mine]));

    expect($captured->options['filter'])->toContain(sprintf('recipients IN [%d]', $recipient->id));
});

it('searches the mail of the services the user belongs to', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $service = Service::factory()->create();
    $recipient->services()->attach($service->id);

    $mine = IncomingMail::factory()->create();
    $mine->services()->attach($service->id);

    $captured = fakeCourrierSearch([$mine->id]);

    $myCourriers = livewire(DashboardPage::class)->instance()->myCourriers;

    expect($myCourriers->pluck('id'))->toContain($mine->id);
    expect($captured->options['filter'])->toContain(
        sprintf('(recipients IN [%d] OR services IN [%d])', $recipient->id, $service->id),
    );
});

it('restricts the mail search to the last 15 days', function (): void {
    $this->freezeTime();

    $user = User::factory()->create();
    $this->actingAs($user);

    Recipient::factory()->create(['username' => $user->username]);

    $captured = fakeCourrierSearch();

    livewire(DashboardPage::class)->assertOk();

    expect($captured->options['filter'])
        ->toContain('mail_date_timestamp >= '.now()->subDays(15)->getTimestamp());
    expect($captured->options['limit'])->toBe(10);
});

it('links each recent news item to its view page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $news = News::factory()->create();

    livewire(DashboardPage::class)
        ->assertSee($news->name)
        ->assertSeeHtml(route('filament.news-panel.resources.news.view', ['record' => $news]));
});

it('links each recent document to its view page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $document = Document::factory()->create();

    livewire(DashboardPage::class)
        ->assertSee($document->name)
        ->assertSeeHtml(route('filament.document-panel.resources.documents.view', ['record' => $document]));
});

it('eager loads the category of each recent document', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    Document::factory()->count(3)->create();

    $latestDocuments = livewire(DashboardPage::class)->instance()->latestDocuments;

    expect($latestDocuments)->toHaveCount(3)
        ->and($latestDocuments->every(fn (Document $document): bool => $document->relationLoaded('category')))->toBeTrue();
});

it('still renders and warns when the mail search is unavailable', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    Recipient::factory()->create(['username' => $user->username]);

    failingCourrierSearch();

    $page = livewire(DashboardPage::class)
        ->assertOk()
        ->assertSee('La recherche de courriers est momentanément indisponible.');

    expect($page->instance()->courrierSearchFailed)->toBeTrue();
    expect($page->instance()->myCourriers)->toBeEmpty();
});

it('lists no mail for a user who is not a known recipient', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $other = IncomingMail::factory()->create();

    fakeCourrierSearch([$other->id]);

    $myCourriers = livewire(DashboardPage::class)->instance()->myCourriers;

    expect($myCourriers)->toBeEmpty();
});
