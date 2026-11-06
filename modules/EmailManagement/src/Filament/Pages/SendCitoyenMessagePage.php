<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Mail\CitoyenMessage;
use AcMarche\EmailManagement\Repository\CitoyenRepository;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Override;

/**
 * Compose a message and queue it, one mail per active citoyen, through the CitoyenMessage mailable.
 */
final class SendCitoyenMessagePage extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

    #[Override]
    protected string $view = 'email-management::filament.pages.send-citoyen-message';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    #[Override]
    protected static ?int $navigationSort = 5;

    #[Override]
    protected static ?string $navigationLabel = 'Envoyer un message';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->isAdministrator() || $user->hasRole(RolesEnum::ROLE_EMAIL_ADMIN->value);
    }

    public function getTitle(): string
    {
        return 'Envoyer un message';
    }

    public function mount(): void
    {
        $this->form->fill([
            'subject' => 'Information importante - Ville de Marche-en-Famenne',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Message')
                    ->description(fn (): string => sprintf('Envoyé à tous les citoyens actifs (%d).', CitoyenRepository::getActiveQuery()->count()))
                    ->schema([
                        TextInput::make('subject')
                            ->label('Objet')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('body')
                            ->label('Message')
                            ->required(),
                    ]),
            ]);
    }

    public function send(): void
    {
        $data = $this->form->getState();

        /** @var array<int, string> $recipients */
        $recipients = CitoyenRepository::getActiveQuery()->pluck('mail')->all();

        $secondsBetweenMails = (int) config('email-management.citoyen_message.seconds_between_mails');

        foreach ($recipients as $index => $recipient) {
            Mail::to($recipient)->later(
                now()->addSeconds($index * $secondsBetweenMails),
                new CitoyenMessage($data['body'], $data['subject']),
            );
        }

        Notification::make()
            ->title('Message mis en file d\'attente')
            ->body(sprintf('%d destinataire(s). Les e-mails partiront via la file d\'attente.', count($recipients)))
            ->success()
            ->send();

        $this->form->fill([
            'subject' => $data['subject'],
        ]);
    }

    /**
     * @return array<int, Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Envoyer')
                ->icon(Heroicon::PaperAirplane)
                ->submit('send')
                ->requiresConfirmation()
                ->modalHeading('Confirmer l\'envoi')
                ->modalDescription(fn (): string => $this->getConfirmationDescription()),
        ];
    }

    private function getConfirmationDescription(): string
    {
        $recipientsCount = CitoyenRepository::getActiveQuery()->count();
        $secondsBetweenMails = (int) config('email-management.citoyen_message.seconds_between_mails');

        return sprintf(
            'Un e-mail sera envoyé à chacun des %d citoyens actifs. Durée estimée de l\'envoi : %d minute(s).',
            $recipientsCount,
            (int) ceil($recipientsCount * $secondsBetweenMails / 60),
        );
    }
}
