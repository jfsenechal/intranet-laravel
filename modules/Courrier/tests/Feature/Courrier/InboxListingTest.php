<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Exception\ImapException;
use AcMarche\Courrier\Filament\Pages\Inbox;
use AcMarche\Courrier\Repository\ImapRepository;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Carbon\CarbonInterface;
use DirectoryTree\ImapEngine\Laravel\ImapManager;
use DirectoryTree\ImapEngine\Testing\FakeFolder;
use DirectoryTree\ImapEngine\Testing\FakeMailbox;
use DirectoryTree\ImapEngine\Testing\FakeMessage;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

/**
 * A raw MIME message with a plain-text body and, optionally, a PDF attachment.
 */
function inboxMail(int $uid, string $subject, string $from = 'Jean Test <jean@example.com>', ?CarbonInterface $date = null, bool $withAttachment = true): FakeMessage
{
    $headers = 'Date: '.($date ?? now())->toRfc2822String()."\r\n"
        ."From: {$from}\r\n"
        ."Subject: {$subject}\r\n"
        ."MIME-Version: 1.0\r\n";

    if (! $withAttachment) {
        return new FakeMessage(
            uid: $uid,
            contents: $headers."Content-Type: text/plain; charset=UTF-8\r\n\r\nCorps du message\r\n",
        );
    }

    return new FakeMessage(uid: $uid, contents: $headers
        ."Content-Type: multipart/mixed; boundary=\"BOUND\"\r\n"
        ."\r\n"
        ."--BOUND\r\n"
        ."Content-Type: text/plain; charset=UTF-8\r\n\r\n"
        ."Corps du message\r\n"
        ."--BOUND\r\n"
        ."Content-Type: application/pdf; name=\"doc.pdf\"\r\n"
        ."Content-Disposition: attachment; filename=\"doc.pdf\"\r\n"
        ."Content-Transfer-Encoding: base64\r\n\r\n"
        .base64_encode('%PDF-1.4 fake')."\r\n"
        .'--BOUND--'."\r\n");
}

/**
 * @param  array<int, FakeMessage>  $messages
 */
function actAsCpasIndicateur(array $messages): FakeFolder
{
    $folder = new FakeFolder('inbox', messages: $messages);

    resolve(ImapManager::class)->swap('imap_cpas', new FakeMailbox(folders: [$folder]));

    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]));
    test()->actingAs($user);

    return $folder;
}

describe('listing without downloading bodies', function (): void {
    it('reports the attachments of a message without carrying its body', function (): void {
        actAsCpasIndicateur([inboxMail(1, 'Sujet test')]);

        $messages = new ImapRepository('imap_cpas')->getMessages();

        expect($messages)->toHaveCount(1)
            ->and($messages[0]->subject)->toBe('Sujet test')
            ->and($messages[0]->hasAttachments)->toBeTrue()
            ->and($messages[0]->attachmentCount)->toBe(1)
            ->and($messages[0]->attachments[0]->filename)->toBe('doc.pdf')
            ->and($messages[0]->attachments[0]->contentType)->toBe('application/pdf')
            ->and($messages[0]->toArray())->not->toHaveKeys(['html', 'text']);
    });

    it('reads the body of a single message on demand', function (): void {
        actAsCpasIndicateur([inboxMail(7, 'Sujet test')]);

        expect(new ImapRepository('imap_cpas')->getMessageBody(7)['text'])
            ->toContain('Corps du message');
    });

    it('builds the view modal, whose body is resolved from IMAP on mount', function (): void {
        actAsCpasIndicateur([inboxMail(1, 'Sans piece jointe', withAttachment: false)]);

        livewire(Inbox::class)
            ->loadTable()
            ->mountAction(TestAction::make('view')->table('0'))
            ->assertActionMounted(TestAction::make('view')->table('0'))
            ->assertHasNoActionErrors();
    });

    it('fails when asked for the body of an unknown message', function (): void {
        actAsCpasIndicateur([inboxMail(7, 'Sujet test')]);

        new ImapRepository('imap_cpas')->getMessageBody(999);
    })->throws(ImapException::class);
});

describe('searching, sorting and paginating', function (): void {
    it('paginates the listing instead of rendering every message', function (): void {
        actAsCpasIndicateur(
            collect(range(1, 12))
                ->map(fn (int $uid): FakeMessage => inboxMail($uid, "Message {$uid}"))
                ->all()
        );

        $records = livewire(Inbox::class)
            ->loadTable()
            ->instance()
            ->getTableRecords();

        expect($records->total())->toBe(12)
            ->and($records)->toHaveCount(10);
    });

    it('filters the listing by sender and subject', function (): void {
        actAsCpasIndicateur([
            inboxMail(1, 'Facture janvier', 'Compta <compta@example.com>'),
            inboxMail(2, 'Candidature', 'Marie <marie@example.com>'),
        ]);

        livewire(Inbox::class)
            ->loadTable()
            ->searchTable('facture')
            ->assertSee('Facture janvier')
            ->assertDontSee('Candidature');

        livewire(Inbox::class)
            ->loadTable()
            ->searchTable('marie@example.com')
            ->assertSee('Candidature')
            ->assertDontSee('Facture janvier');
    });

    it('sorts on the instant a message arrived, not on its formatted date', function (): void {
        actAsCpasIndicateur([
            inboxMail(1, 'Le plus ancien', date: now()->subDays(3)),
            inboxMail(2, 'Le plus recent', date: now()->subMinutes(5)),
        ]);

        // `defaultSort('date', 'desc')`: newest first.
        expect(
            livewire(Inbox::class)->loadTable()->instance()->getTableRecords()->first()['subject']
        )->toBe('Le plus recent');

        expect(
            livewire(Inbox::class)
                ->loadTable()
                ->sortTable('date')
                ->instance()
                ->getTableRecords()
                ->first()['subject']
        )->toBe('Le plus ancien');
    });
});

describe('caching the listing', function (): void {
    it('serves the listing from the cache rather than refetching on every request', function (): void {
        $folder = actAsCpasIndicateur([inboxMail(1, 'Premier message')]);

        livewire(Inbox::class)->loadTable()->assertSee('Premier message');

        $folder->addMessage(inboxMail(2, 'Message arrive ensuite'));

        livewire(Inbox::class)->loadTable()->assertDontSee('Message arrive ensuite');
    });

    it('refetches the listing when the user asks for it', function (): void {
        $folder = actAsCpasIndicateur([inboxMail(1, 'Premier message')]);

        livewire(Inbox::class)->loadTable()->assertSee('Premier message');

        $folder->addMessage(inboxMail(2, 'Message arrive ensuite'));

        livewire(Inbox::class)
            ->loadTable()
            ->callAction(TestAction::make('refresh')->table())
            ->assertSee('Message arrive ensuite');
    });

    it('refetches the listing after a mail is processed, since it leaves the mailbox', function (): void {
        Storage::fake(config('courrier.storage.disk'));

        $folder = actAsCpasIndicateur([inboxMail(1, 'A traiter')]);

        $component = livewire(Inbox::class)->loadTable();

        // Stands in for the message leaving the mailbox: the fake only flags it
        // as deleted, so the refetch is observed through what the mailbox gains
        // rather than what it loses.
        $folder->addMessage(inboxMail(2, 'Message arrive ensuite'));

        $component
            ->callAction(TestAction::make('process')->table('0'), [
                'mail_date' => now()->format('Y-m-d'),
                'sender' => 'ACME SA',
            ])
            ->assertHasNoActionErrors()
            ->assertSee('Message arrive ensuite');
    });
});
