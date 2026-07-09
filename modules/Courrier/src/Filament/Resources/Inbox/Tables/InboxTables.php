<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Inbox\Tables;

use AcMarche\Courrier\Dto\EmailMessage;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Exception\ImapException;
use AcMarche\Courrier\Filament\Resources\Inbox\Schemas\InboxForm;
use AcMarche\Courrier\Filament\Resources\Inbox\Schemas\InboxInfolist;
use AcMarche\Courrier\Handler\IncomingMailHandler;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Repository\ImapRepository;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

final class InboxTables
{
    public static function configure(Table $table, ?string $mailbox = null): Table
    {
        $imapRepository = $mailbox !== null ? new ImapRepository($mailbox) : null;

        return $table
            ->records(fn (): array => self::getRecords($imapRepository))
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
                    ->schema(fn (?array $record): array => InboxInfolist::getEmailViewSchema($record, $mailbox ?? 'imap_ville'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer'),
                Action::make('process')
                    ->label('Traiter')
                    ->color('gray')
                    ->icon(Heroicon::DocumentArrowDown)
                    ->visible(fn (array $record): bool => ($record['attachment_count'] ?? 0) === 1)
                    ->modalHeading(fn (array $record): string => $record['attachments'][0]['filename'] ?? 'Pièce jointe')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->fillForm(fn (array $record): array => [
                        'reference_number' => self::defaultReferenceNumber(),
                        'sender' => '',
                        'mail_date' => now(),
                        'description' => $record['subject'] ?? '',
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
                    ->action(function (array $data, array $record) use ($mailbox): void {
                        IncomingMailHandler::handleIncomingMailCreation(
                            $data,
                            $record['uid'],
                            1,
                            0,
                            $record['attachments'][0]['filename'] ?? 'Sans nom',
                            $record['attachments'][0]['content_type'] ?? 'application/octet-stream',
                            $mailbox ?? 'imap_ville'
                        );
                    })
                    ->modalSubmitActionLabel('Enregistrer le courrier'),
            ])
            ->toolbarActions([
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

                            $livewire->deselectAllTableRecords();
                            $livewire->flushCachedTableRecords();

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
     * @return array<int, array<string, mixed>>
     */
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

    private static function getRecords(?ImapRepository $imapRepository): array
    {
        if (! $imapRepository instanceof ImapRepository) {
            Notification::make()
                ->title('Aucune boîte mail configurée')
                ->body('Aucun service courrier ne vous est attribué pour accéder à une boîte mail.')
                ->danger()
                ->send();

            return [];
        }

        try {
            return array_map(
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
    }
}
