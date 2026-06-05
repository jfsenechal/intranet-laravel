<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Pages\IncomingMailSearch;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Search\MeiliIndexer;
use AcMarche\Courrier\Search\MeiliSearcher;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

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
            ->toBe(sprintf('destinataires IN [%d]', $recipient->id));
    });

    it('restricts a recipient with services to their mail or linked services', function (): void {
        $user = User::factory()->create();
        $recipient = Recipient::factory()->create(['username' => $user->username]);
        $service = Service::factory()->create();
        $recipient->services()->attach($service->id);

        expect(app(MeiliSearcher::class)->policyFilter($user))
            ->toBe(sprintf('(destinataires IN [%d] OR services IN [%d])', $recipient->id, $service->id));
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
            'sender' => 'ACME <SA>',
            'is_registered' => true,
        ]);
        $primary = Recipient::factory()->create();
        $copy = Recipient::factory()->create();
        $service = Service::factory()->create();
        $mail->recipients()->attach($primary->id, ['is_primary' => true]);
        $mail->recipients()->attach($copy->id, ['is_primary' => false]);
        $mail->services()->attach($service->id, ['is_primary' => true]);

        $document = app(MeiliIndexer::class)->createDocument($mail->fresh());

        expect($document)
            ->id->toBe($mail->id)
            ->numero->toBe('2026-42')
            ->expediteur->toBe('ACME SA')
            ->recommande->toBeTrue();

        expect($document['destinataires'])->toEqualCanonicalizing([$primary->id, $copy->id]);
        expect($document['services'])->toEqual([$service->id]);
        expect($document['original'])->toContain($primary->full_name, $service->name);
        expect($document['copie'])->toContain($copy->full_name);
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
