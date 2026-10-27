<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Mail\CitoyenMessage;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
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
 * Compose a message and queue it, one mail per address, through the CitoyenMessage mailable.
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
    protected static ?int $navigationSort = 11;

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
                    ->schema([
                        TagsInput::make('recipients')
                            ->label('Destinataires')
                            ->helperText('Une adresse par étiquette. Validez avec Entrée, une virgule ou un espace.')
                            ->splitKeys(['Enter', ',', ' '])
                            ->required()
                            ->nestedRecursiveRules(['email']),
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
        $recipients = array_values(array_unique($data['recipients']));

        foreach ($recipients as $recipient) {
            Mail::to($recipient)->queue(new CitoyenMessage($data['body'], $data['subject']));
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
                ->modalDescription('Un e-mail sera envoyé à chaque destinataire.'),
        ];
    }
}
