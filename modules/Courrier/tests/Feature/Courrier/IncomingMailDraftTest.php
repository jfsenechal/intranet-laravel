<?php

declare(strict_types=1);

use AcMarche\Courrier\Ai\IncomingMailAgent;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\DraftIncomingMails;
use AcMarche\Courrier\Filament\Pages\Inbox;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\EditIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\ListIncomingMails;
use AcMarche\Courrier\Jobs\AnalyzeInboxMessagesJob;
use AcMarche\Courrier\Mail\IncomingMailDraftsReady;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use AcMarche\Security\Enums\RolesEnum as SecurityRolesEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;
use DirectoryTree\ImapEngine\Laravel\ImapManager;
use DirectoryTree\ImapEngine\Testing\FakeFolder;
use DirectoryTree\ImapEngine\Testing\FakeMailbox;
use DirectoryTree\ImapEngine\Testing\FakeMessage;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

/**
 * A MIME message carrying the stamped scan the mail room feeds into the Inbox,
 * so the analysis in these tests reads a real document.
 */
function fakeScannedMail(int $uid, string $filename = 'doc.pdf'): FakeMessage
{
    $pdf = (string) file_get_contents(dirname(__DIR__, 2).'/Fixtures/courrier-scanne-cachet.pdf');

    $mime = 'Date: '.now()->toRfc2822String()."\r\n"
        ."From: Copieur <copieur@marche.be>\r\n"
        ."Subject: SKM_C250\r\n"
        ."MIME-Version: 1.0\r\n"
        ."Content-Type: multipart/mixed; boundary=\"BOUND\"\r\n"
        ."\r\n"
        ."--BOUND\r\n"
        ."Content-Type: text/plain; charset=UTF-8\r\n\r\n"
        ."\r\n"
        ."--BOUND\r\n"
        ."Content-Type: application/pdf; name=\"{$filename}\"\r\n"
        ."Content-Disposition: attachment; filename=\"{$filename}\"\r\n"
        ."Content-Transfer-Encoding: base64\r\n\r\n"
        .chunk_split(base64_encode($pdf))
        .'--BOUND--'."\r\n";

    return new FakeMessage(uid: $uid, contents: $mime);
}

/**
 * @return array{reference_number: string, services: array<int, string>, sender: string, description: string, is_registered: bool, has_acknowledgment: bool}
 */
function draftSuggestion(): array
{
    return [
        'reference_number' => '002693',
        'services' => ['RH'],
        'sender' => 'Sandrine Simon',
        'description' => 'Demande de congé',
        'is_registered' => false,
        'has_acknowledgment' => false,
    ];
}

/**
 * The bulk analysis is the same trial as the form completion, so it needs the
 * intranet role on top of the courrier one.
 */
function actAsInboxAdmin(bool $intranetAdmin = true): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]));

    if ($intranetAdmin) {
        $user->roles()->attach(Role::firstOrCreate(['name' => SecurityRolesEnum::INTRANET_ADMIN->value]));
    }

    test()->actingAs($user);

    return $user;
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    Storage::fake(config('courrier.storage.disk'));
});

it('queues the analysis of the selected messages', function (): void {
    resolve(ImapManager::class)->swap('imap_ville', new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [fakeScannedMail(1), fakeScannedMail(2, 'autre.pdf')]),
    ]));

    $user = actAsInboxAdmin();

    Queue::fake();

    livewire(Inbox::class)
        ->call('loadTable')
        ->callTableBulkAction('analyze', ['0', '1'])
        ->assertNotified();

    Queue::assertPushed(
        AnalyzeInboxMessagesJob::class,
        fn (AnalyzeInboxMessagesJob $job): bool => $job->mailbox === 'imap_ville'
            && $job->userId === $user->id
            && count($job->messages) === 2
            && $job->messages[0]['filename'] === 'doc.pdf',
    );
});

it('hides the bulk analysis without the intranet role', function (): void {
    resolve(ImapManager::class)->swap('imap_ville', new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [fakeScannedMail(1)]),
    ]));

    actAsInboxAdmin(intranetAdmin: false);

    Queue::fake();

    livewire(Inbox::class)
        ->call('loadTable')
        ->assertTableBulkActionHidden('analyze')
        // Hiding it is the gate, as on the form: Filament will not mount an
        // action that is not visible, so a hand-crafted Livewire call cannot
        // reach the model either.
        ->mountTableBulkAction('analyze', ['0'])
        ->assertActionNotMounted();

    Queue::assertNotPushed(AnalyzeInboxMessagesJob::class);
});

it('creates a draft from each analysed attachment and mails the author', function (): void {
    resolve(ImapManager::class)->swap('imap_ville', new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [fakeScannedMail(1)]),
    ]));

    $user = actAsInboxAdmin();
    $service = Service::factory()->create([
        'name' => 'Ressources Humaines',
        'initials' => 'RH',
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    IncomingMailAgent::fake([draftSuggestion()]);
    Mail::fake();

    (new AnalyzeInboxMessagesJob(
        'imap_ville',
        [['uid' => 1, 'index' => 0, 'filename' => 'doc.pdf', 'mime' => 'application/pdf']],
        $user->id,
        DepartmentCourrierEnum::VILLE,
    ))->handle(app(AcMarche\Courrier\Ai\IncomingMailAnalyzer::class));

    $draft = IncomingMail::query()->where('reference_number', '002693')->firstOrFail();

    expect($draft->is_draft)->toBeTrue()
        ->and($draft->sender)->toBe('Sandrine Simon')
        ->and($draft->user_add)->toBe($user->username)
        ->and($draft->department)->toBe(DepartmentCourrierEnum::VILLE->value)
        ->and($draft->services->pluck('id')->all())->toBe([$service->id])
        ->and($draft->attachments)->toHaveCount(1);

    Mail::assertSent(
        IncomingMailDraftsReady::class,
        fn (IncomingMailDraftsReady $mail): bool => $mail->hasTo($user->email)
            && $mail->drafts->count() === 1
            && $mail->failures === [],
    );
});

it('reports the documents the analysis could not read', function (): void {
    resolve(ImapManager::class)->swap('imap_ville', new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [fakeScannedMail(1)]),
    ]));

    $user = actAsInboxAdmin();

    IncomingMailAgent::fake([draftSuggestion()]);
    Mail::fake();

    (new AnalyzeInboxMessagesJob(
        'imap_ville',
        // No message carries this uid, so fetching the attachment throws.
        [['uid' => 99, 'index' => 0, 'filename' => 'introuvable.pdf', 'mime' => 'application/pdf']],
        $user->id,
        DepartmentCourrierEnum::VILLE,
    ))->handle(app(AcMarche\Courrier\Ai\IncomingMailAnalyzer::class));

    expect(IncomingMail::query()->count())->toBe(0);

    Mail::assertSent(
        IncomingMailDraftsReady::class,
        fn (IncomingMailDraftsReady $mail): bool => $mail->drafts->isEmpty()
            && $mail->failures === ['introuvable.pdf'],
    );
});

it('keeps drafts out of the day listing', function (): void {
    actAsInboxAdmin();

    $draft = IncomingMail::factory()->draft()->create([
        'mail_date' => today(),
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);
    $published = IncomingMail::factory()->create([
        'mail_date' => today(),
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(ListIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$published])
        ->assertCanNotSeeTableRecords([$draft]);
});

it('keeps drafts out of the recipient notifications', function (): void {
    $recipient = Recipient::factory()->create(['email' => 'destinataire@marche.be']);

    $draft = IncomingMail::factory()->draft()->create([
        'mail_date' => today(),
        'is_notified' => false,
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);
    $draft->recipients()->attach($recipient->id, ['is_primary' => true]);

    $mails = (new IncomingMailRepository())->getIncomingMailsForRecipient($recipient, today());

    expect($mails)->toHaveCount(0);
});

it('publishes the draft and opens the next one when it is validated', function (): void {
    $user = actAsInboxAdmin();

    $first = IncomingMail::factory()->draft()->create([
        'user_add' => $user->username,
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);
    $second = IncomingMail::factory()->draft()->create([
        'user_add' => $user->username,
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(EditIncomingMail::class, ['record' => $first->id])
        ->fillForm(['reference_number' => 'VERIFIE-1', 'sender' => 'Sandrine Simon'])
        ->call('validateDraft')
        ->assertHasNoFormErrors()
        ->assertNotified('Courrier validé')
        ->assertRedirect(
            AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource::getUrl(
                'edit',
                ['record' => $second],
            ),
        );

    expect($first->refresh()->is_draft)->toBeFalse()
        ->and($first->reference_number)->toBe('VERIFIE-1')
        ->and($second->refresh()->is_draft)->toBeTrue();
});

it('returns to the listing once the last draft is validated', function (): void {
    $user = actAsInboxAdmin();

    $only = IncomingMail::factory()->draft()->create([
        'user_add' => $user->username,
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(EditIncomingMail::class, ['record' => $only->id])
        ->fillForm(['reference_number' => 'VERIFIE-2', 'sender' => 'Sandrine Simon'])
        ->call('validateDraft')
        ->assertRedirect(DraftIncomingMails::getUrl());

    expect($only->refresh()->is_draft)->toBeFalse();
});

it('refuses to publish a draft the form rejects', function (): void {
    $user = actAsInboxAdmin();

    $draft = IncomingMail::factory()->draft()->create([
        'user_add' => $user->username,
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(EditIncomingMail::class, ['record' => $draft->id])
        ->fillForm(['reference_number' => '', 'sender' => 'Sandrine Simon'])
        ->call('validateDraft')
        ->assertHasFormErrors(['reference_number' => 'required'])
        ->assertNoRedirect();

    expect($draft->refresh()->is_draft)->toBeTrue();
});

it('offers a way back into the draft queue from the listing', function (): void {
    $user = actAsInboxAdmin();

    $draft = IncomingMail::factory()->draft()->create([
        'user_add' => $user->username,
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(ListIncomingMails::class)
        ->assertActionVisible(TestAction::make('reviewDrafts'));

    // Nothing to review once it is validated.
    $draft->update(['is_draft' => false]);

    livewire(ListIncomingMails::class)
        ->assertActionHidden(TestAction::make('reviewDrafts'));
});

it('lists every draft of the department on the drafts page', function (): void {
    $user = actAsInboxAdmin();

    $mine = IncomingMail::factory()->draft()->create([
        'user_add' => $user->username,
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);
    // The queue is shared: a colleague's draft is work to do, not their own.
    $colleagues = IncomingMail::factory()->draft()->create([
        'user_add' => 'un_collegue',
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);
    $otherDepartment = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::CPAS->value,
    ]);
    $published = IncomingMail::factory()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(DraftIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$mine, $colleagues])
        ->assertCanNotSeeTableRecords([$otherDepartment, $published]);
});

it('counts the drafts left in the navigation badge', function (): void {
    actAsInboxAdmin();

    expect(DraftIncomingMails::getNavigationBadge())->toBeNull();

    IncomingMail::factory()->draft()->count(3)->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    expect(DraftIncomingMails::getNavigationBadge())->toBe('3');
});

it('closes the drafts page to a user outside the ai trial', function (): void {
    actAsInboxAdmin(intranetAdmin: false);

    expect(DraftIncomingMails::canAccess())->toBeFalse();
});

it('discards a draft from the drafts page', function (): void {
    actAsInboxAdmin();

    $draft = IncomingMail::factory()->draft()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(DraftIncomingMails::class)
        ->loadTable()
        ->callTableBulkAction('delete', [$draft->getKey()]);

    expect(IncomingMail::query()->whereKey($draft->getKey())->exists())->toBeFalse();
});
