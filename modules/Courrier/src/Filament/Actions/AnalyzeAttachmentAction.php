<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Actions;

use AcMarche\Courrier\Ai\IncomingMailAnalyzer;
use AcMarche\Courrier\Dto\MailAnalysis;
use AcMarche\Courrier\Dto\MailSuggestion;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Repository\ImapRepository;
use AcMarche\Courrier\Repository\ServiceRepository;
use AcMarche\Courrier\Search\SuggestsMailRouting;
use AcMarche\Security\Enums\RolesEnum;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Colors\Color;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

/**
 * Reads the mail attached to the form and asks the AI to fill in the reference
 * number, the services it is addressed to, the sender, the description and the
 * two option toggles.
 *
 * The file is looked up in the order it becomes available: the pending upload
 * first (the user has just picked it), then the attachment already stored on
 * the record when editing, and finally the IMAP message the Inbox flow works
 * from, which has no local copy and is streamed to a temporary file.
 *
 * The completion is still being trialled, so the button is only shown to
 * intranet administrators.
 */
final class AnalyzeAttachmentAction
{
    /**
     * @param  array{uid: int, index: int, mailbox: string}|null  $imapSource  set by the Inbox flow, where the
     *                                                                         document only exists on the IMAP server
     */
    public static function make(?array $imapSource = null): Action
    {
        return Action::make('analyzeAttachment')
            ->label('Compléter avec l\'IA')
            ->icon('tabler-sparkles')
            ->color(Color::Indigo)
            ->link()
            ->visible(fn (IncomingMail|array|null $record): bool => self::isUnderTrialFor()
                && self::isEncodingRatherThanEditing($record))
            // $record is an IncomingMail on the resource form, but the Inbox
            // flow mounts this action inside a modal whose record is the raw
            // IMAP message array, so the parameter has to accept both.
            ->action(function (Get $schemaGet, Set $schemaSet, IncomingMail|array|null $record) use ($imapSource): void {
                $temporaryPath = null;

                try {
                    [$path, $mime, $temporaryPath] = self::resolveFile(
                        $schemaGet,
                        $record instanceof IncomingMail ? $record : null,
                        $imapSource,
                    );

                    if ($path === null) {
                        Notification::make()
                            ->title('Aucun fichier à analyser')
                            ->body('Sélectionnez d\'abord le fichier du courrier.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $analysis = app(IncomingMailAnalyzer::class)->analyze($path, $mime);
                } catch (Throwable $e) {
                    report($e);

                    Notification::make()
                        ->title('Analyse impossible')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                } finally {
                    if ($temporaryPath !== null) {
                        @unlink($temporaryPath);
                    }
                }

                self::applySuggestion($analysis->suggestion, $schemaGet, $schemaSet);
                self::applyRouting($analysis, $schemaGet, $schemaSet);
            });
    }

    /**
     * Who gets to try the AI completion. Hiding the action also refuses it when
     * mounted, so this is the only gate the feature needs while it is a trial.
     *
     * Shared with the Inbox bulk analysis, which is the same trial.
     */
    public static function isUnderTrialFor(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasRole(RolesEnum::INTRANET_ADMIN->value);
    }

    /**
     * Whether the form is encoding a mail rather than correcting one already
     * encoded.
     *
     * The AI reads a document to turn it into a courrier, which is a thing that
     * happens once. On a courrier a human has already validated, its suggestions
     * would only offer to overwrite decisions someone took on purpose, so the
     * button is not offered there at all. A draft still counts as encoding: it
     * has a record, but nobody has read it yet.
     *
     * The Inbox passes the raw IMAP message array, which is as "new" as it gets.
     */
    private static function isEncodingRatherThanEditing(IncomingMail|array|null $record): bool
    {
        return ! $record instanceof IncomingMail || $record->is_draft;
    }

    /**
     * Fill in the services and recipients that comparable mail was routed to.
     *
     * Where a courrier goes is almost never written on the courrier — the name
     * of the person it ends up with appears in the letter about one time in
     * eight — so this is retrieved from the mail already encoded rather than
     * read by the model. Only the two best candidates per field are proposed,
     * and only into a field the user has left empty: the rest of the ranking is
     * made of alternatives, not of extra destinations.
     */
    private static function applyRouting(MailAnalysis $analysis, Get $schemaGet, Set $schemaSet): void
    {
        if ($analysis->documentText === '') {
            return;
        }

        $routing = app(SuggestsMailRouting::class)->suggest(
            $analysis->documentText,
            $analysis->suggestion->sender,
            DepartmentScope::getAssignableDepartment()?->value,
        );

        if ($routing->recipientIds !== [] && blank($schemaGet('primary_recipients'))) {
            $schemaSet('primary_recipients', $routing->topRecipientIds());
        }

        // The services read off the reception stamp were applied first and are
        // the better source — they are what the mail room itself wrote down —
        // so the retrieval only speaks for a field they left empty.
        if ($routing->serviceIds !== [] && blank($schemaGet('primary_services'))) {
            $schemaSet('primary_services', $routing->topServiceIds());
        }
    }

    /**
     * Push the suggestion into the form. The toggles always take the suggested
     * value, but an empty sender or description is left alone rather than
     * wiping what the user already typed.
     *
     * The reference number and the services are only proposed when their field
     * is still empty: the number must not overwrite one already encoded nor the
     * sequential number the CPAS department is given by default, and a routing
     * the user has already chosen outranks a stamp read by a model.
     */
    private static function applySuggestion(MailSuggestion $suggestion, Get $schemaGet, Set $schemaSet): void
    {
        if ($suggestion->referenceNumber !== '' && mb_trim((string) $schemaGet('reference_number')) === '') {
            $schemaSet('reference_number', $suggestion->referenceNumber);
        }

        self::applyServices($suggestion, $schemaGet, $schemaSet);

        if ($suggestion->sender !== '') {
            $schemaSet('sender', $suggestion->sender);
        }

        if ($suggestion->description !== '') {
            $schemaSet('description', $suggestion->description);
        }

        $schemaSet('is_registered', $suggestion->isRegistered);
        $schemaSet('has_acknowledgment', $suggestion->hasAcknowledgment);

        Notification::make()
            ->title('Formulaire complété')
            ->body('Vérifiez les informations proposées avant d\'enregistrer.')
            ->success()
            ->send();
    }

    /**
     * The stamp names the services the mail is routed to by their initials
     * (« 2693 - RH (CEE) »). They are looked up in the department the mail is
     * being encoded in, since each department keeps its own list. Codes that
     * match no service, or more than one, are dropped rather than guessed: the
     * user picks those from the select.
     */
    private static function applyServices(MailSuggestion $suggestion, Get $schemaGet, Set $schemaSet): void
    {
        if ($suggestion->services === [] || filled($schemaGet('primary_services'))) {
            return;
        }

        $services = ServiceRepository::findIdsByCodes(
            $suggestion->services,
            DepartmentScope::getAssignableDepartment(),
        );

        if ($services !== []) {
            $schemaSet('primary_services', $services);
        }
    }

    /**
     * @param  array{uid: int, index: int, mailbox: string}|null  $imapSource
     * @return array{0: string|null, 1: string, 2: string|null} path, mime type, and the temporary file to
     *                                                          delete afterwards when there is one
     */
    private static function resolveFile(Get $schemaGet, ?IncomingMail $record, ?array $imapSource): array
    {
        $upload = $schemaGet('attachment_file');
        if (is_array($upload)) {
            $upload = Arr::first($upload);
        }

        if ($upload instanceof TemporaryUploadedFile) {
            return [$upload->getRealPath(), (string) $upload->getMimeType(), null];
        }

        $attachment = $record?->attachments->first();
        if ($attachment !== null && $attachment->path !== null) {
            $disk = Storage::disk(config('courrier.storage.disk'));

            if ($disk->exists($attachment->path)) {
                return [$disk->path($attachment->path), (string) $attachment->mime, null];
            }
        }

        if ($imapSource !== null) {
            return self::downloadImapAttachment($imapSource);
        }

        return [null, '', null];
    }

    /**
     * @param  array{uid: int, index: int, mailbox: string}  $imapSource
     * @return array{0: string, 1: string, 2: string}
     */
    private static function downloadImapAttachment(array $imapSource): array
    {
        $attachment = (new ImapRepository($imapSource['mailbox']))
            ->getAttachment($imapSource['uid'], $imapSource['index']);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'courrier_ai_');

        if ($temporaryPath === false) {
            throw new RuntimeException('Impossible de créer un fichier temporaire pour l\'analyse.');
        }

        $extension = $attachment->extension();
        if ($extension !== null && $extension !== '') {
            // pdftotext and Tesseract both branch on the extension, so keep it.
            $renamed = $temporaryPath.'.'.$extension;
            rename($temporaryPath, $renamed);
            $temporaryPath = $renamed;
        }

        file_put_contents($temporaryPath, $attachment->contents());

        return [$temporaryPath, $attachment->contentType(), $temporaryPath];
    }
}
