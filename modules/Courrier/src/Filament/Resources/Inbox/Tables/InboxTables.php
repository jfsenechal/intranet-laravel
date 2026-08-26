<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Inbox\Tables;

use AcMarche\Courrier\Dto\EmailMessage;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Exception\ImapException;
use AcMarche\Courrier\Filament\Actions\AnalyzeAttachmentAction;
use AcMarche\Courrier\Filament\Resources\Inbox\Schemas\InboxForm;
use AcMarche\Courrier\Filament\Resources\Inbox\Schemas\InboxInfolist;
use AcMarche\Courrier\Handler\IncomingMailHandler;
use AcMarche\Courrier\Jobs\AnalyzeInboxMessagesJob;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Repository\ImapRepository;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class InboxTables
{
    private const int RECORDS_CACHE_TTL = 60;

    public static function configure(Table $table, ?string $mailbox = null): Table
    {
        $imapRepository = $mailbox !== null ? new ImapRepository($mailbox) : null;

        return $table
            ->records(fn (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int $recordsPerPage): LengthAwarePaginator => self::paginateRecords(
                self::getRecords($imapRepository, $mailbox),
                $search,
                $sortColumn,
                $sortDirection,
                $page,
                $recordsPerPage,
            ))
            ->columns([
                IconColumn::make('has_attachments')
                    ->label('')
                    ->width('40px')
                    ->icon(fn (array $record): ?string => $record['has_attachments'] ? 'tabler-paperclip' : null)
                    ->color('gray'),
                TextColumn::make('date')
                    ->label('Date')
                    ->width('150px')
                    ->sortable(),
                TextColumn::make('from')
                    ->label('Expéditeur')
                    ->width('250px')
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Objet')
                    ->searchable()
                    ->wrap(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->color('gray')
                    ->icon(Heroicon::Eye)
                    ->visible(fn (array $record): bool => ($record['attachment_count'] ?? 0) !== 1)
                    ->modalHeading(fn (array $record): string => $record['subject'] ?? 'Sans objet')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->schema(fn (?array $record): array => InboxInfolist::getEmailViewSchema(
                        $record,
                        $mailbox ?? 'imap_ville',
                        fn (int $uid): array => self::getMessageBody($imapRepository, $uid),
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer'),
                Action::make('process')
                    ->label('Traiter')
                    ->color('gray')
                    ->icon(Heroicon::DocumentArrowDown)
                    ->visible(fn (array $record): bool => ($record['attachment_count'] ?? 0) === 1)
                    ->modalHeading(fn (array $record): string => $record['attachments'][0]['filename'] ?? 'Pièce jointe')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->fillForm(fn (): array => [
                        'reference_number' => self::defaultReferenceNumber(),
                        'sender' => '',
                        'mail_date' => now(),
                        'description' => '',
                        'is_registered' => false,
                        'has_acknowledgment' => false,
                    ])
                    ->schema(fn (array $record): array => InboxForm::getAttachmentFormSchema(
                        $record['uid'],
                        0,
                        $record['attachments'][0]['content_type'] ?? 'application/octet-stream',
                        $record['attachments'][0]['filename'] ?? 'Sans nom',
                        $mailbox ?? 'imap_ville'
                    ))
                    ->action(function (array $data, array $record, HasTable $livewire) use ($mailbox): void {
                        IncomingMailHandler::handleIncomingMailCreation(
                            $data,
                            $record['uid'],
                            1,
                            0,
                            $record['attachments'][0]['filename'] ?? 'Sans nom',
                            $record['attachments'][0]['content_type'] ?? 'application/octet-stream',
                            $mailbox ?? 'imap_ville'
                        );

                        self::forgetRecords($mailbox);
                        $livewire->resetTable();
                    })
                    ->modalSubmitActionLabel('Enregistrer le courrier'),
            ])
            ->headerActions([
                Action::make('refresh')
                    ->label('Actualiser')
                    ->icon('tabler-refresh')
                    ->color('gray')
                    ->action(function (HasTable $livewire) use ($mailbox): void {
                        self::forgetRecords($mailbox);
                        $livewire->resetTable();
                    }),
            ])
            ->toolbarActions([
                self::analyzeBulkAction($mailbox ?? 'imap_ville'),
                BulkAction::make('delete')
                    ->label('Supprimer')
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records, HasTable $livewire) use ($imapRepository): void {
                        if (! $imapRepository instanceof ImapRepository) {
                            return;
                        }

                        try {
                            $imapRepository->deleteMessages($records->pluck('uid')->toArray());

                            self::forgetRecords($imapRepository->mailboxName());

                            $livewire->deselectAllTableRecords();
                            $livewire->resetTable();

                            Notification::make()
                                ->title('Messages supprimés')
                                ->success()
                                ->send();
                        } catch (ImapException $exception) {
                            Notification::make()
                                ->title('Erreur lors de la suppression')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->paginated([10, 25, 50]);
    }

    /**
     * Hand a selection of messages to the AI, which encodes each one as a draft
     * courrier the user then verifies.
     *
     * Only messages carrying exactly one attachment qualify, like the "Traiter"
     * action: with none there is nothing to read, and with several nothing says
     * which one is the courrier. The rest of the selection is reported rather
     * than silently dropped.
     *
     * The work is queued: an analysis takes tens of seconds, so a selection of
     * any size would time the request out. The user is mailed when the batch is
     * done.
     */
    private static function analyzeBulkAction(string $mailbox): BulkAction
    {
        return BulkAction::make('analyze')
            ->label('Traiter avec l\'IA')
            ->icon('tabler-sparkles')
            ->color(Color::Indigo)
            ->visible(AnalyzeAttachmentAction::isUnderTrialFor(...))
            ->requiresConfirmation()
            ->modalHeading('Analyser les courriers sélectionnés')
            ->modalDescription(
                'Chaque pièce jointe sera lue par l\'IA et enregistrée comme brouillon de courrier. '
                .'Vous recevrez un mail avec un lien pour les vérifier et les valider.'
            )
            ->modalSubmitActionLabel('Lancer l\'analyse')
            ->action(function (Collection $records, HasTable $livewire) use ($mailbox): void {
                $user = Auth::user();

                if (! $user instanceof User) {
                    return;
                }

                $messages = self::analysableMessages($records);
                $skipped = $records->count() - count($messages);

                if ($messages === []) {
                    Notification::make()
                        ->title('Aucun message analysable')
                        ->body('Seuls les messages comportant une seule pièce jointe peuvent être analysés.')
                        ->warning()
                        ->send();

                    return;
                }

                AnalyzeInboxMessagesJob::dispatch(
                    $mailbox,
                    $messages,
                    $user->id,
                    DepartmentScope::getAssignableDepartment(),
                );

                $livewire->deselectAllTableRecords();

                Notification::make()
                    ->title(sprintf('Analyse lancée pour %d message(s)', count($messages)))
                    ->body(
                        'Vous recevrez un mail dès que les brouillons seront prêts.'
                        .($skipped > 0 ? sprintf(' %d message(s) ignoré(s) : pièce jointe absente ou multiple.', $skipped) : '')
                    )
                    ->success()
                    ->send();
            });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $records
     * @return list<array{uid: int, index: int, filename: string, mime: string}>
     */
    private static function analysableMessages(Collection $records): array
    {
        return $records
            ->filter(fn (array $record): bool => ($record['attachment_count'] ?? 0) === 1)
            ->map(fn (array $record): array => [
                'uid' => (int) $record['uid'],
                'index' => 0,
                'filename' => $record['attachments'][0]['filename'] ?? 'Sans nom',
                'mime' => $record['attachments'][0]['content_type'] ?? 'application/octet-stream',
            ])
            ->values()
            ->all();
    }

    /**
     * Suggested reference number for the process form. CPAS mail is numbered
     * sequentially, so it is pre-filled; other departments enter it manually.
     */
    private static function defaultReferenceNumber(): string
    {
        return DepartmentScope::getAssignableDepartment() === DepartmentCourrierEnum::CPAS
            ? (string) IncomingMail::nextCpasReferenceNumber()
            : '';
    }

    /**
     * Search, sort and paginate the listing in memory.
     *
     * A custom data source gets none of this for free: Filament hands the state
     * to `records()` and expects the closure to apply it, so without this the
     * search field, the sortable date column and the page size selector were
     * inert and every message was rendered on one page.
     *
     * @param  array<int, array<string, mixed>>  $records
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private static function paginateRecords(
        array $records,
        ?string $search,
        ?string $sortColumn,
        ?string $sortDirection,
        int $page,
        int $recordsPerPage,
    ): LengthAwarePaginator {
        $messages = collect($records)
            ->when(
                filled($search),
                fn (Collection $messages): Collection => $messages->filter(
                    fn (array $record): bool => Str::contains(
                        $record['from'].' '.$record['subject'],
                        (string) $search,
                        ignoreCase: true,
                    ),
                ),
            )
            ->sortBy(
                // The date column is formatted for humans (`d/m/Y H:i`), so it
                // does not sort as a string; the record carries the instant.
                $sortColumn === 'date' ? 'timestamp' : ($sortColumn ?? 'timestamp'),
                SORT_REGULAR,
                ($sortDirection ?? 'desc') === 'desc',
            )
            ->values();

        return new LengthAwarePaginator(
            $messages->forPage($page, $recordsPerPage),
            total: $messages->count(),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    /**
     * The listing, cached for the duration of a burst of interactions.
     *
     * Filament rebuilds a custom data source on every Livewire request, and a
     * user opening the "Traiter" modal, filling the form and submitting it
     * costs several: without this, each one refetched the whole mailbox.
     * Anything that removes a message from the mailbox calls
     * `forgetRecords()`, and the "Actualiser" action lets the user pull new
     * mail in before the entry expires on its own.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRecords(?ImapRepository $imapRepository, ?string $mailbox): array
    {
        if (! $imapRepository instanceof ImapRepository) {
            Notification::make()
                ->title('Aucune boîte mail configurée')
                ->body('Aucun service courrier ne vous est attribué pour accéder à une boîte mail.')
                ->danger()
                ->send();

            return [];
        }

        $cached = Cache::get(self::recordsCacheKey($mailbox));

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $records = array_map(
                fn (EmailMessage $message): array => $message->toArray(),
                $imapRepository->getMessages()
            );
        } catch (ImapException $e) {
            Notification::make()
                ->title('Erreur de connexion IMAP')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return [];
        }

        Cache::put(self::recordsCacheKey($mailbox), $records, self::RECORDS_CACHE_TTL);

        return $records;
    }

    private static function forgetRecords(?string $mailbox): void
    {
        Cache::forget(self::recordsCacheKey($mailbox));
    }

    private static function recordsCacheKey(?string $mailbox): string
    {
        return 'courrier.inbox.messages.'.($mailbox ?? 'imap_ville');
    }

    /**
     * The body of the message being viewed, fetched now because the listing
     * does not carry one.
     *
     * @return array{html: ?string, text: ?string}
     */
    private static function getMessageBody(?ImapRepository $imapRepository, int $uid): array
    {
        if (! $imapRepository instanceof ImapRepository) {
            return ['html' => null, 'text' => null];
        }

        try {
            return $imapRepository->getMessageBody($uid);
        } catch (ImapException $e) {
            report($e);

            return ['html' => null, 'text' => null];
        }
    }
}
