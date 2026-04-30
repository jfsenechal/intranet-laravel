<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Filament\Resources\NotifyRecipients\Schemas\NotifyRecipientsForm;
use AcMarche\Courrier\Filament\Resources\NotifyRecipients\Tables\NotifyRecipientsTables;
use AcMarche\Courrier\Jobs\SendIncomingMailNotificationJob;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use AcMarche\Courrier\Repository\RecipientRepository;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Override;
use UnitEnum;

final class NotifyRecipients extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?string $mail_date = null;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-mail-forward';

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Notifier les destinataires';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    #[Override]
    protected string $view = 'courrier::filament.pages.notify-recipients';

    public static function canAccess(array $parameters = []): bool
    {
        return Gate::check('courrier-administrator');
    }

    public function mount(): void
    {
        $this->mail_date = now()->format('Y-m-d');
    }

    public function getTitle(): string
    {
        return 'Notifier les destinataires';
    }

    public function form(Schema $schema): Schema
    {
        return NotifyRecipientsForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return NotifyRecipientsTables::configure($table, $this->mail_date);
    }

    public function loadPreviewData(): void
    {
        if (! $this->mail_date) {
            // $this->previewData = [];

            return;
        }

        $incomingMailRepository = new IncomingMailRepository();
        $mailDate = Date::parse($this->mail_date);
        $recipients = RecipientRepository::getWithEmail();

        $preview = [];

        foreach ($recipients as $recipient) {
            $mails = $incomingMailRepository->getIncomingMailsForRecipient($recipient, $mailDate);

            if ($mails->isNotEmpty()) {
                $preview[] = [
                    'recipient' => $recipient,
                    'mails' => $mails,
                    'has_index_role' => $incomingMailRepository->recipientHasIndexRole($recipient),
                ];
            }
        }

        //  $this->previewData = $preview;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendNotifications')
                ->label('Envoyer les notifications')
                ->icon('tabler-send')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Confirmer l\'envoi')
                ->modalDescription(fn (): string => sprintf(
                    'Vous allez envoyer des notifications a %d destinataire(s). Cette action est irreversible.',
                    count($this->previewData)
                ))
                ->modalSubmitActionLabel('Envoyer')
                ->disabled(fn (): bool => empty($this->previewData))
                ->action(function (): void {
                    if (! $this->mail_date) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Veuillez selectionner une date.')
                            ->danger()
                            ->send();

                        return;
                    }

                    dispatch(new SendIncomingMailNotificationJob(Date::parse($this->mail_date)));

                    Notification::make()
                        ->title('Notifications en cours d\'envoi')
                        ->body('Les notifications seront envoyees en arriere-plan.')
                        ->success()
                        ->send();

                    $this->loadPreviewData();
                }),
        ];
    }
}
