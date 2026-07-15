<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\IncomingMailSearch;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Search\MeiliIndexer;
use AcMarche\Courrier\Search\MeiliSearcher;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Search\SearchResult;

use function Pest\Livewire\livewire;

/**
 * Build a Meili client that returns the given hits and captures the search
 * options (filter, sort, …) passed to the index for assertion.
 *
 * @param  array<int, array<string, mixed>>  $hits
 * @return array{0: Client, 1: stdClass}
 */
function captureMeiliSearch(array $hits = []): array
{
    $captured = new stdClass();
    $captured->options = [];

    $result = Mockery::mock(SearchResult::class);
    $result->shouldReceive('getHits')->andReturn($hits);

    $index = Mockery::mock(Indexes::class);
    $index->shouldReceive('search')
        ->once()
        ->andReturnUsing(function (string $query, array $options) use ($captured, $result): SearchResult {
            $captured->options = $options;

            return $result;
        });

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('index')->andReturn($index);

    return [$client, $captured];
}

beforeEach(function (): void {
    // The Meilisearch master key is provided via env in real environments;
    // set a placeholder so the client can be instantiated under test.
    config()->set('app.meilisearch.master_key', 'test-master-key');
});

describe('MeiliSearcher policy filter', function (): void {
    it('lets an administrator search every mail without restriction', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);

        expect(app(MeiliSearcher::class)->policyFilter($user))->toBe('');
    });

    it('restricts a recipient without services to their own mail', function (): void {
        $user = User::factory()->create();
        $recipient = Recipient::factory()->create(['username' => $user->username]);

        expect(app(MeiliSearcher::class)->policyFilter($user))
            ->toBe(sprintf('recipients IN [%d]', $recipient->id));
    });

    it('restricts a recipient with services to their mail or linked services', function (): void {
        $user = User::factory()->create();
        $recipient = Recipient::factory()->create(['username' => $user->username]);
        $service = Service::factory()->create();
        $recipient->services()->attach($service->id);

        expect(app(MeiliSearcher::class)->policyFilter($user))
            ->toBe(sprintf('(recipients IN [%d] OR services IN [%d])', $recipient->id, $service->id));
    });

    it('scopes an index user to the mail of their department', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
        $user->roles()->attach($role);

        expect(app(MeiliSearcher::class)->policyFilter($user))
            ->toBe(sprintf('department IN ["%s"]', DepartmentCourrierEnum::VILLE->value));
    });

    it('scopes an index user by department even when they are also a recipient', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
        $user->roles()->attach($role);
        Recipient::factory()->create(['username' => $user->username]);

        expect(app(MeiliSearcher::class)->policyFilter($user))
            ->toBe(sprintf('department IN ["%s"]', DepartmentCourrierEnum::VILLE->value));
    });

    it('denies a user that is not a known recipient', function (): void {
        $user = User::factory()->create();

        expect(app(MeiliSearcher::class)->policyFilter($user))->toBeNull();
    });
});

describe('MeiliIndexer document', function (): void {
    it('builds a search document from an incoming mail', function (): void {
        $mail = IncomingMail::factory()->create([
            'reference_number' => '2026-42',
            'sender' => 'ACME SA',
            'is_registered' => true,
        ]);
        $primary = Recipient::factory()->create();
        $copy = Recipient::factory()->create();
        $service = Service::factory()->create();
        $mail->recipients()->attach($primary->id, ['is_primary' => true]);
        $mail->recipients()->attach($copy->id, ['is_primary' => false]);
        $mail->services()->attach($service->id, ['is_primary' => true]);

        $mail->load(['recipients', 'services']);
        $mail->setRelation('attachments', collect());

        $document = app(MeiliIndexer::class)->createDocument($mail);

        expect($document)
            ->id->toBe($mail->id)
            ->reference_number->toBe('2026-42')
            ->sender->toBe('ACME SA')
            ->is_registered->toBeTrue();

        expect($document['recipients'])->toEqualCanonicalizing([$primary->id, $copy->id]);
        expect($document['services'])->toEqual([$service->id]);
        expect($document['original'])->toContain($primary->full_name, $service->name);
        expect($document['copie'])->toContain($copy->full_name);
    });
});

describe('MeiliSearcher reference and category filters', function (): void {
    it('matches a bare reference by id or reference number', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);
        [$client, $captured] = captureMeiliSearch([['id' => 42]]);
        $searcher = new MeiliSearcher();
        $searcher->client = $client;

        $ids = $searcher->searchIds('', $user, ['reference' => '42']);

        expect($ids)->toBe([42]);
        expect($captured->options['filter'])->toContain('(id = 42 OR reference_number = "42")');
    });

    it('matches a textual reference by reference number only', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);
        [$client, $captured] = captureMeiliSearch();
        $searcher = new MeiliSearcher();
        $searcher->client = $client;

        $searcher->searchIds('', $user, ['reference' => '2026-42']);

        expect($captured->options['filter'])->toContain('reference_number = "2026-42"');
    });

    it('filters by category id', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);
        [$client, $captured] = captureMeiliSearch();
        $searcher = new MeiliSearcher();
        $searcher->client = $client;

        $searcher->searchIds('', $user, ['category' => 7]);

        expect($captured->options['filter'])->toContain('category_id = 7');
    });

    it('filters by department', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);
        [$client, $captured] = captureMeiliSearch();
        $searcher = new MeiliSearcher();
        $searcher->client = $client;

        $searcher->searchIds('', $user, ['department' => DepartmentCourrierEnum::CPAS->value]);

        expect($captured->options['filter'])->toContain('department = "Cpas"');
    });

    it('omits reference and category clauses when they are not provided', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);
        [$client, $captured] = captureMeiliSearch();
        $searcher = new MeiliSearcher();
        $searcher->client = $client;

        $searcher->searchIds('hello', $user);

        expect($captured->options['filter'])->toBe([]);
    });
});

it('mounts the advanced search page with an empty result set before searching', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    $admin = User::factory()->create(['is_administrator' => true]);
    IncomingMail::factory()->count(3)->create();

    $this->actingAs($admin);

    livewire(IncomingMailSearch::class)
        ->assertOk()
        ->assertCanNotSeeTableRecords(IncomingMail::all());
});
