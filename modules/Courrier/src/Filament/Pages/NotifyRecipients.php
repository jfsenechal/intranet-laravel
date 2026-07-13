<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Filament\Resources\NotifyRecipients\Schemas\NotifyRecipientsForm;
use AcMarche\Courrier\Filament\Resources\NotifyRecipients\Tables\NotifyRecipientsTables;
use AcMarche\Courrier\Jobs\SendIncomingMailNotificationJob;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use AcMarche\Courrier\Repository\RecipientRepository;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Override;
use UnitEnum;

final class NotifyRecipients extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?string $mail_date = null;

    public bool $force_notify = false;

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

    private array $previewData = [];

    public static function canAccess(array $parameters = []): bool
    {
        return Gate::check('courrier-administrator');
    }

    public function mount(): void
    {
        $this->mail_date = now()->format('Y-m-d');
        $this->loadPreviewData();
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
            $this->previewData = [];

            return;
        }

        $incomingMailRepository = new IncomingMailRepository();
        $mailDate = Date::parse($this->mail_date);
        $recipients = RecipientRepository::getWithEmail();

        $preview = [];

        $department = $this->currentAdminDepartment();

        foreach ($recipients as $recipient) {
            $mails = $incomingMailRepository->getIncomingMailsForRecipient($recipient, $mailDate, false, $department);

            if ($mails->isNotEmpty()) {
                $preview[] = [
                    'recipient' => $recipient,
                    'mails' => $mails,
                    'has_index_role' => $incomingMailRepository->recipientHasIndexRole($recipient),
                ];
            }
        }

        $this->previewData = $preview;
    }

    /**
     * Number of recipients that would receive a notification for the selected
     * date. When $includeNotified is true, the count reflects a forced send
     * (already notified mail is counted too).
     */
    public function countRecipientsToNotify(bool $includeNotified): int
    {
        if (! $this->mail_date) {
            return 0;
        }

        $incomingMailRepository = new IncomingMailRepository();
        $mailDate = Date::parse($this->mail_date);
        $department = $this->currentAdminDepartment();

        return RecipientRepository::getWithEmail()
            ->filter(fn ($recipient): bool => $incomingMailRepository
                ->getIncomingMailsForRecipient($recipient, $mailDate, $includeNotified, $department)
                ->isNotEmpty())
            ->count();
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
                ->modalDescription('Cette action est irreversible.')
                ->modalSubmitActionLabel('Envoyer')
                ->schema([
                    Text::make(fn (): string => sprintf(
                        'Vous allez envoyer des notifications a %d destinataire(s).',
                        $this->countRecipientsToNotify($this->force_notify),
                    )),
                ])
               // ->disabled(fn (): bool => empty($this->previewData))
                ->action(function (): void {
                    if (! $this->mail_date) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Veuillez selectionner une date.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $department = $this->currentAdminDepartment();

                    if (! $department instanceof DepartmentCourrierEnum) {
                        Notification::make()
                            ->title('Envoi impossible')
                            ->body('Aucun département administrateur ne vous est associé. Vous ne pouvez pas envoyer de notifications.')
                            ->danger()
                            ->send();

                        return;
                    }

                    dispatch(new SendIncomingMailNotificationJob(
                        Date::parse($this->mail_date),
                        $this->force_notify,
                        $this->senderAddress(),
                        $department,
                    ));

                    Notification::make()
                        ->title('Notifications en cours d\'envoi')
                        ->body('Les notifications seront envoyees en arriere-plan.')
                        ->success()
                        ->send();

                    $this->loadPreviewData();
                }),
        ];
    }

    /**
     * The department the triggering admin administers, used to restrict the
     * notified mail. A Cpas admin only notifies Cpas mail, a Ville admin only
     * Ville mail. Returns null for a global administrator, who notifies every
     * department.
     */
    private function currentAdminDepartment(): ?DepartmentCourrierEnum
    {
        $user = Auth::user();

        return $user instanceof User ? $user->getCourrierAdminDepartment() : null;
    }

    /**
     * The address of the admin triggering the send, used as the mail sender.
     *
     * Resolved here in the web request because the queued job runs without an
     * authenticated user; null falls back to the configured default address.
     */
    private function senderAddress(): ?Address
    {
        $user = Auth::user();

        return $user instanceof User
            ? new Address($user->email, $user->fullNameAsString())
            : null;
    }
}
