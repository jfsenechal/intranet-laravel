<?php

namespace AcMarche\Courrier\Filament\Resources\Inbox\Tables;

use AcMarche\Courrier\Dto\EmailMessage;
use AcMarche\Courrier\Exception\ImapException;
use AcMarche\Courrier\Filament\Resources\Inbox\Schemas\InboxForm;
use AcMarche\Courrier\Filament\Resources\Inbox\Schemas\InboxInfolist;
use AcMarche\Courrier\Handler\IncomingMailHandler;
use AcMarche\Courrier\Repository\ImapRepository;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

final class InboxTables
{
    public static function configure(Table $table): Table
    {
        $imapRepository = new ImapRepository();

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
                    ->schema(fn (?array $record): array => InboxInfolist::getEmailViewSchema($record))
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
                        'reference_number' => '',
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
                        str_starts_with($record['attachments'][0]['content_type'] ?? '', 'image/')
                            || ($record['attachments'][0]['content_type'] ?? '') === 'application/pdf'
                    ))
                    ->action(function (array $data, array $record): void {
                        IncomingMailHandler::handleIncomingMailCreation(
                            $data,
                            $record['uid'],
                            1,
                            0,
                            $record['attachments'][0]['filename'] ?? 'Sans nom',
                            $record['attachments'][0]['content_type'] ?? 'application/octet-stream'
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
                    ->action(function (Collection $records) use ($imapRepository): void {
                        try {
                            $imapRepository->deleteMessages($records->pluck('uid')->toArray());

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
    private static function getRecords(ImapRepository $imapRepository): array
    {
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
