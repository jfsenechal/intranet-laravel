<?php

declare(strict_types=1);

use AcMarche\Courrier\Ai\IncomingMailAgent;
use AcMarche\Courrier\Dto\RoutingSuggestion;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\Inbox;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\EditIncomingMail;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Search\SimilarMailFinder;
use AcMarche\Courrier\Search\SuggestsMailRouting;
use AcMarche\Security\Enums\RolesEnum as SecurityRolesEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;
use DirectoryTree\ImapEngine\Laravel\ImapManager;
use DirectoryTree\ImapEngine\Testing\FakeFolder;
use DirectoryTree\ImapEngine\Testing\FakeMailbox;
use DirectoryTree\ImapEngine\Testing\FakeMessage;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

/**
 * An IMAP message carrying the fixture courrier, so the Traiter modal has a
 * real document to analyse.
 */
function routingInboxMessage(int $uid = 1): FakeMessage
{
    $pdf = (string) file_get_contents(dirname(__DIR__, 2).'/Fixtures/courrier-recommande.pdf');

    $mime = 'Date: '.now()->toRfc2822String()."\r\n"
        ."From: Copieur <copieur@marche.be>\r\n"
        ."Subject: SKM_C250\r\n"
        ."MIME-Version: 1.0\r\n"
        ."Content-Type: multipart/mixed; boundary=\"BOUND\"\r\n"
        ."\r\n"
        ."--BOUND\r\n"
        ."Content-Type: application/pdf; name=\"courrier.pdf\"\r\n"
        ."Content-Disposition: attachment; filename=\"courrier.pdf\"\r\n"
        ."Content-Transfer-Encoding: base64\r\n\r\n"
        .chunk_split(base64_encode($pdf))
        .'--BOUND--'."\r\n";

    return new FakeMessage(uid: $uid, contents: $mime);
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));

    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]));
    $user->roles()->attach(Role::firstOrCreate(['name' => SecurityRolesEnum::INTRANET_ADMIN->value]));
    $this->actingAs($user);
});

/**
 * The finder with its Meilisearch round trip replaced, so the tests exercise
 * the tally and the form wiring rather than the index.
 */
function fakeFinder(RoutingSuggestion $suggestion): void
{
    $fake = Mockery::mock(SuggestsMailRouting::class);
    $fake->shouldReceive('suggestFor')->andReturn($suggestion);
    $fake->shouldReceive('suggest')->andReturn($suggestion);

    app()->instance(SuggestsMailRouting::class, $fake);
}

it('returns nothing when the document yielded no text', function (): void {
    $suggestion = (new SimilarMailFinder())->suggest('');

    expect($suggestion->isEmpty())->toBeTrue();
});

it('returns nothing when the letter holds no distinctive word', function (): void {
    // Only boilerplate and numbers: every term is dropped, leaving no query.
    $suggestion = (new SimilarMailFinder())->suggest('Madame, Monsieur, veuillez agréer 2026 12345');

    expect($suggestion->isEmpty())->toBeTrue();
});

it('survives the index being unreachable', function (): void {
    config()->set('app.meilisearch.host', 'http://127.0.0.1:1');

    $suggestion = (new SimilarMailFinder())->suggest(
        'permis urbanisme toiture ardoises maison unifamiliale rénovation façade',
    );

    expect($suggestion->isEmpty())->toBeTrue();
});

it('routes a draft with the best candidates read off similar mail', function (): void {
    $best = Recipient::factory()->create(['last_name' => 'Simon', 'first_name' => 'Sandrine']);
    $second = Recipient::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Jean']);
    $third = Recipient::factory()->create(['last_name' => 'Martin', 'first_name' => 'Luc']);

    fakeFinder(new RoutingSuggestion([$best->id, $second->id, $third->id], []));

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);

    // Only the two best are written in: the rest of the ranking is made of
    // alternatives, and every extra row is one the user has to delete.
    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertSchemaStateSet(['primary_recipients' => [$best->id, $second->id]]);
});

it('leaves the field empty when nothing is retrieved', function (): void {
    Recipient::factory()->create();

    fakeFinder(RoutingSuggestion::empty());

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertSchemaStateSet(['primary_recipients' => [], 'primary_services' => []]);
});

it('does not look anything up for a mail with no extracted text', function (): void {
    $fake = Mockery::mock(SuggestsMailRouting::class);
    $fake->shouldNotReceive('suggestFor');
    app()->instance(SuggestsMailRouting::class, $fake);

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => null,
    ]);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertSchemaStateSet(['primary_recipients' => []]);
});

it('does not retrieve a routing the user has already encoded', function (): void {
    $fake = Mockery::mock(SuggestsMailRouting::class);
    $fake->shouldNotReceive('suggestFor');
    app()->instance(SuggestsMailRouting::class, $fake);

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);
    $recipient = Recipient::factory()->create();
    $service = Service::factory()->create();
    $mail->recipients()->attach($recipient->id, ['is_primary' => true]);
    $mail->services()->attach($service->id, ['is_primary' => true]);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertSchemaStateSet([
            'primary_recipients' => [$recipient->id],
            'primary_services' => [$service->id],
        ]);
});

it('never touches a courrier a human has already validated', function (): void {
    $fake = Mockery::mock(SuggestsMailRouting::class);
    $fake->shouldNotReceive('suggestFor');
    app()->instance(SuggestsMailRouting::class, $fake);

    $mail = IncomingMail::factory()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertSchemaStateSet(['primary_recipients' => []]);
});

it('saves the retrieved routing with the draft', function (): void {
    $recipient = Recipient::factory()->create();

    fakeFinder(new RoutingSuggestion([$recipient->id], []));

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->fillForm(['sender' => 'SPW Territoire'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($mail->refresh()->sender)->toBe('SPW Territoire')
        ->and($mail->recipients->pluck('id')->all())->toBe([$recipient->id]);
});

it('fills the routing fields in the inbox modal, where no record exists yet', function (): void {
    IncomingMailAgent::fake([[
        'reference_number' => '002693',
        'services' => [],
        'sender' => 'SPRL Toitures Dubois',
        'description' => 'Demande de permis',
        'is_registered' => false,
        'has_acknowledgment' => false,
    ]]);

    $suggested = Recipient::factory()->create(['last_name' => 'Simon', 'first_name' => 'Sandrine']);
    $service = Service::factory()->create();
    fakeFinder(new RoutingSuggestion([$suggested->id], [$service->id]));

    resolve(ImapManager::class)->swap('imap_ville', new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [routingInboxMessage()]),
    ]));

    livewire(Inbox::class)
        ->call('loadTable')
        ->callAction([
            TestAction::make('process')->table('0'),
            TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'),
        ])
        ->assertSchemaStateSet([
            'primary_recipients' => [$suggested->id],
            'primary_services' => [$service->id],
        ]);
});

it('offers the ai button while encoding, and never on an encoded courrier', function (): void {
    $draft = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);
    $encoded = IncomingMail::factory()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    $action = TestAction::make('analyzeAttachment')->schemaComponent('ai-completion');

    livewire(CreateIncomingMail::class)->assertActionVisible($action);
    livewire(EditIncomingMail::class, ['record' => $draft->id])->assertActionVisible($action);
    // A hidden action is removed from the schema outright, which is also what
    // stops a hand-crafted Livewire call from reaching it.
    livewire(EditIncomingMail::class, ['record' => $encoded->id])->assertActionDoesNotExist($action);
});
