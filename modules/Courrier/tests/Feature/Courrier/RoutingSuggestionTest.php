<?php

declare(strict_types=1);

use AcMarche\Courrier\Ai\IncomingMailAgent;
use AcMarche\Courrier\Dto\RoutingSuggestion;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\Inbox;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\EditIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas\IncomingMailForm;
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
use Filament\Forms\Components\Select;

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

it('shows the suggested recipients above the full list', function (): void {
    $suggested = Recipient::factory()->create(['last_name' => 'Simon', 'first_name' => 'Sandrine']);
    $other = Recipient::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Jean']);

    fakeFinder(new RoutingSuggestion([$suggested->id], []));

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);

    $options = livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->instance()
        ->form
        ->getComponent(fn ($component): bool => $component instanceof Select
            && $component->getName() === 'primary_recipients')
        ->getOptions();

    expect($options)->toHaveKeys(['Suggérés (courriers similaires)', 'Tous les destinataires'])
        ->and($options['Suggérés (courriers similaires)'])->toHaveKey($suggested->id)
        // A candidate must not also appear in the list below it.
        ->and($options['Tous les destinataires'])->not->toHaveKey($suggested->id)
        ->and($options['Tous les destinataires'])->toHaveKey($other->id);
});

it('leaves the select flat when nothing is suggested', function (): void {
    $recipient = Recipient::factory()->create();

    fakeFinder(RoutingSuggestion::empty());

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);

    $options = livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->instance()
        ->form
        ->getComponent(fn ($component): bool => $component instanceof Select
            && $component->getName() === 'primary_recipients')
        ->getOptions();

    expect($options)->toHaveKey($recipient->id)
        ->and($options)->not->toHaveKey('Suggérés (courriers similaires)');
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
        ->assertSchemaStateSet([IncomingMailForm::SUGGESTED_RECIPIENTS => []]);
});

it('does not suggest a routing the user has already encoded', function (): void {
    $fake = Mockery::mock(SuggestsMailRouting::class);
    $fake->shouldNotReceive('suggestFor');
    app()->instance(SuggestsMailRouting::class, $fake);

    $mail = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'content' => 'permis urbanisme toiture ardoises rénovation façade maison',
    ]);
    $mail->recipients()->attach(Recipient::factory()->create()->id, ['is_primary' => true]);
    $mail->services()->attach(Service::factory()->create()->id, ['is_primary' => true]);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertSchemaStateSet([IncomingMailForm::SUGGESTED_SERVICES => []]);
});

it('never writes the suggestion keys to the record', function (): void {
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
        ->and($mail->getAttributes())->not->toHaveKey(IncomingMailForm::SUGGESTED_RECIPIENTS);
});

it('suggests a routing in the inbox modal, where no record exists yet', function (): void {
    IncomingMailAgent::fake([[
        'reference_number' => '002693',
        'services' => [],
        'sender' => 'SPRL Toitures Dubois',
        'description' => 'Demande de permis',
        'is_registered' => false,
        'has_acknowledgment' => false,
    ]]);

    $suggested = Recipient::factory()->create(['last_name' => 'Simon', 'first_name' => 'Sandrine']);
    fakeFinder(new RoutingSuggestion([$suggested->id], []));

    resolve(ImapManager::class)->swap('imap_ville', new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [routingInboxMessage()]),
    ]));

    livewire(Inbox::class)
        ->call('loadTable')
        ->callAction([
            TestAction::make('process')->table('0'),
            TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'),
        ])
        ->assertSchemaStateSet([IncomingMailForm::SUGGESTED_RECIPIENTS => [$suggested->id]]);
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
