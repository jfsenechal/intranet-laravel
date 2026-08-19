<?php

declare(strict_types=1);

use AcMarche\Courrier\Ai\IncomingMailAgent;
use AcMarche\Courrier\Ai\IncomingMailAnalyzer;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Search\AttachmentOcr;
use AcMarche\Security\Enums\RolesEnum as SecurityRolesEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Laravel\Ai\Files\LocalImage;
use Laravel\Ai\Prompts\AgentPrompt;

use function Pest\Livewire\livewire;

/**
 * A one-page PDF with a real text layer, so `pdftotext` reads it without OCR.
 */
function fixtureMailPath(): string
{
    return dirname(__DIR__, 2).'/Fixtures/courrier-recommande.pdf';
}

/**
 * A one-page scan (no text layer), stamped by the mail room like the documents
 * the MFP feeds into the Inbox.
 */
function fixtureScannedMailPath(): string
{
    return dirname(__DIR__, 2).'/Fixtures/courrier-scanne-cachet.pdf';
}

/**
 * @return array{reference_number: string, services: array<int, string>, sender: string, description: string, is_registered: bool, has_acknowledgment: bool}
 */
function fakeSuggestion(): array
{
    return [
        'reference_number' => '002686',
        'services' => ['RH', 'CEE'],
        'sender' => 'SPRL Toitures Dubois',
        'description' => "Demande de permis d'urbanisme pour une toiture",
        'is_registered' => true,
        'has_acknowledgment' => true,
    ];
}

/**
 * The AI completion is a trial feature reserved for intranet administrators, so
 * every test that reaches the button needs that role on top of the courrier one.
 */
function actAsCourrierUser(bool $intranetAdmin = true): User
{
    $user = User::factory()->create();
    // Role names are unique, and a test may build a second user.
    $user->roles()->attach(Role::firstOrCreate(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]));

    if ($intranetAdmin) {
        $user->roles()->attach(Role::firstOrCreate(['name' => SecurityRolesEnum::INTRANET_ADMIN->value]));
    }

    test()->actingAs($user);

    return $user;
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));

    actAsCourrierUser();
});

it('prompts the agent with the text extracted from the pdf', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    $suggestion = (new IncomingMailAnalyzer(new AttachmentOcr(enabled: true)))
        ->analyze(fixtureMailPath(), 'application/pdf');

    expect($suggestion->referenceNumber)->toBe('002686')
        ->and($suggestion->services)->toBe(['RH', 'CEE'])
        ->and($suggestion->sender)->toBe('SPRL Toitures Dubois')
        ->and($suggestion->description)->toBe("Demande de permis d'urbanisme pour une toiture")
        ->and($suggestion->isRegistered)->toBeTrue()
        ->and($suggestion->hasAcknowledgment)->toBeTrue();

    IncomingMailAgent::assertPrompted(
        fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'SPRL Toitures Dubois')
            && str_contains($prompt->prompt, 'RECOMMANDE AVEC ACCUSE DE RECEPTION')
    );
});

it('still sends the page when no text can be extracted', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    (new IncomingMailAnalyzer(new AttachmentOcr(enabled: false)))
        ->analyze(fixtureMailPath(), 'application/pdf');

    IncomingMailAgent::assertPrompted(
        fn (AgentPrompt $prompt): bool => $prompt->attachments->first() instanceof LocalImage
            && $prompt->prompt === 'Analyse le courrier joint.'
    );
});

it('refuses a file it can neither read nor send', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    $path = sys_get_temp_dir().'/courrier-test.docx';
    file_put_contents($path, 'not a real docx');

    expect(fn () => (new IncomingMailAnalyzer(new AttachmentOcr(enabled: true)))->analyze(
        $path,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ))->toThrow(RuntimeException::class);

    @unlink($path);

    IncomingMailAgent::assertNeverPrompted();
});

it('fills the form fields from the uploaded file', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'sender' => '',
            'description' => '',
            'attachment_file' => UploadedFile::fake()->createWithContent(
                'courrier.pdf',
                (string) file_get_contents(fixtureMailPath()),
            ),
        ])
        ->callAction(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'))
        ->assertNotified('Formulaire complété')
        ->assertSchemaStateSet([
            'reference_number' => '002686',
            'sender' => 'SPRL Toitures Dubois',
            'description' => "Demande de permis d'urbanisme pour une toiture",
            'is_registered' => true,
            'has_acknowledgment' => true,
        ]);
});

it('shows the button to an intranet administrator', function (): void {
    livewire(CreateIncomingMail::class)
        ->assertActionVisible(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'));
});

it('hides the button, and refuses to run it, without the intranet role', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    actAsCourrierUser(intranetAdmin: false);

    $page = livewire(CreateIncomingMail::class)
        ->assertActionDoesNotExist(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'));

    // Hiding it is the gate: Filament will not resolve an action that is not
    // in the schema, so a hand-crafted Livewire call cannot reach the model.
    expect(fn () => $page->callAction(
        TestAction::make('analyzeAttachment')->schemaComponent('ai-completion')
    ))->toThrow(ActionNotResolvableException::class);

    IncomingMailAgent::assertNeverPrompted();
});

it('warns instead of prompting when no file is attached', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    livewire(CreateIncomingMail::class)
        ->callAction(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'))
        ->assertNotified('Aucun fichier à analyser');

    IncomingMailAgent::assertNeverPrompted();
});

it('keeps what the user typed when the model finds nothing', function (): void {
    IncomingMailAgent::fake([[
        'reference_number' => '',
        'services' => [],
        'sender' => '',
        'description' => '',
        'is_registered' => false,
        'has_acknowledgment' => false,
    ]]);

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'reference_number' => '1234',
            'sender' => 'Saisi à la main',
            'description' => 'Description manuelle',
            'attachment_file' => UploadedFile::fake()->createWithContent(
                'courrier.pdf',
                (string) file_get_contents(fixtureMailPath()),
            ),
        ])
        ->callAction(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'))
        ->assertSchemaStateSet([
            'reference_number' => '1234',
            'sender' => 'Saisi à la main',
            'description' => 'Description manuelle',
        ]);
});

it('renders the first page as an image so the stamp can be read', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    (new IncomingMailAnalyzer(new AttachmentOcr(enabled: true)))
        ->analyze(fixtureScannedMailPath(), 'application/pdf');

    $rendered = null;

    IncomingMailAgent::assertPrompted(function (AgentPrompt $prompt) use (&$rendered): bool {
        $attachment = $prompt->attachments->first();
        $rendered = $attachment instanceof LocalImage ? $attachment->path : null;

        // The OCR text still travels with the image: it covers the pages the
        // model is not shown.
        return $attachment instanceof LocalImage
            && str_contains($prompt->prompt, 'Demande de permis');
    });

    expect($rendered)->toEndWith('.png')
        ->and(file_exists((string) $rendered))->toBeFalse();
});

it('routes the mail to the services stamped on it', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    $humanResources = Service::factory()->create(['name' => 'Ressources Humaines', 'initials' => 'RH']);
    $childhood = Service::factory()->create(['name' => 'Coordination Education Enfance', 'initials' => 'CEE']);

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'attachment_file' => UploadedFile::fake()->createWithContent(
                'courrier.pdf',
                (string) file_get_contents(fixtureMailPath()),
            ),
        ])
        ->callAction(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'))
        ->assertSchemaStateSet(['primary_services' => [$humanResources->id, $childhood->id]]);
});

it('leaves out a stamped code that matches several services', function (): void {
    IncomingMailAgent::fake([[...fakeSuggestion(), 'services' => ['MUS']]]);

    Service::factory()->create(['name' => 'Musée', 'initials' => 'MUS']);
    Service::factory()->create(['name' => 'Conservatoire de Musique', 'initials' => 'MUS']);

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'attachment_file' => UploadedFile::fake()->createWithContent(
                'courrier.pdf',
                (string) file_get_contents(fixtureMailPath()),
            ),
        ])
        ->callAction(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'))
        ->assertSchemaStateSet(['primary_services' => []]);
});

it('keeps the services the user already picked', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    Service::factory()->create(['name' => 'Ressources Humaines', 'initials' => 'RH']);
    $chosen = Service::factory()->create(['name' => 'Service Travaux', 'initials' => 'ST']);

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'primary_services' => [$chosen->id],
            'attachment_file' => UploadedFile::fake()->createWithContent(
                'courrier.pdf',
                (string) file_get_contents(fixtureMailPath()),
            ),
        ])
        ->callAction(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'))
        ->assertSchemaStateSet(['primary_services' => [$chosen->id]]);
});

it('keeps the reference number already encoded', function (): void {
    IncomingMailAgent::fake([fakeSuggestion()]);

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'reference_number' => '000001',
            'attachment_file' => UploadedFile::fake()->createWithContent(
                'courrier.pdf',
                (string) file_get_contents(fixtureMailPath()),
            ),
        ])
        ->callAction(TestAction::make('analyzeAttachment')->schemaComponent('ai-completion'))
        ->assertSchemaStateSet(['reference_number' => '000001']);
});
