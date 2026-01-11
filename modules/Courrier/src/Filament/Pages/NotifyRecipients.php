<?php

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Filament\Resources\NotifyRecipients\Schemas\NotifyRecipientsForm;
use AcMarche\Courrier\Filament\Resources\NotifyRecipients\Tables\NotifyRecipientsTables;
use AcMarche\Courrier\Jobs\SendIncomingMailNotificationJob;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use AcMarche\Courrier\Repository\RecipientRepository;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

final class NotifyRecipients extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?string $mail_date = null;

    protected static string|null|BackedEnum $navigationIcon = 'tabler-mail-forward';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Notifier les destinataires';

    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    protected string $view = 'courrier::filament.pages.notify-recipients';

    public static function canAccess(array $parameters = []): bool
    {
        return Gate::check('courrier-index');
    }

    public function mount(): void
    {
        $this->mail_date = now()->format('Y-m-d');
    }

    public function getTitle(): string|Htmlable
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
        if (!$this->mail_date) {
            //$this->previewData = [];

            return;
        }

        $incomingMailRepository = new IncomingMailRepository();
        $mailDate = Carbon::parse($this->mail_date);
        $recipients = RecipientRepository::getActiveAndWithEmail();

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
                ->modalDescription(fn(): string => sprintf(
                    'Vous allez envoyer des notifications a %d destinataire(s). Cette action est irreversible.',
                    count($this->previewData)
                ))
                ->modalSubmitActionLabel('Envoyer')
                ->disabled(fn(): bool => empty($this->previewData))
                ->action(function (): void {
                    if (!$this->mail_date) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Veuillez selectionner une date.')
                            ->danger()
                            ->send();

                        return;
                    }

                    SendIncomingMailNotificationJob::dispatch(
                        Carbon::parse($this->mail_date)
                    );

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
