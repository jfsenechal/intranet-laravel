<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Actions;

use AcMarche\Agent\Mail\WelcomeMail;
use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

final class SendWelcomeMailAction
{
    public static function make(): Action
    {
        return Action::make('sendWelcomeMail')
            ->label('Mot de bienvenue')
            ->icon(Heroicon::Envelope)
            ->color('success')
            ->visible(fn (): bool => Auth::user()?->can('update', Profile::class) === true)
            ->modalHeading(fn (Profile $record): string => 'Mot de bienvenue pour '.$record->fullName())
            ->modalDescription('Envoie les informations de connexion à l\'agent, avec le courrier de bienvenue en pièce jointe.')
            ->modalSubmitActionLabel('Envoyer')
            ->schema([
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->helperText('Le mot de passe de l\'utilisateur, communiqué dans le courrier de bienvenue.')
                    ->required()
                    ->rule(Password::min(12)),
                Textarea::make('notes')
                    ->label('Remarques')
                    ->rows(5),
            ])
            ->action(function (array $data, Profile $record): void {
                $recipient = $record->user?->email ?: config('agent.informatique_email');

                if (empty($recipient)) {
                    Notification::make()
                        ->title('Aucun destinataire')
                        ->body('L\'agent n\'a pas d\'adresse mail et l\'adresse du service informatique n\'est pas configurée.')
                        ->danger()
                        ->send();

                    return;
                }

                $mail = Mail::to($recipient);

                if ($copies = self::copies($record)) {
                    $mail->cc($copies);
                }

                $mail->send(new WelcomeMail($record, $data['password'], $data['notes'] ?? null));

                Notification::make()
                    ->title('Le mot de bienvenue a bien été envoyé')
                    ->success()
                    ->send();
            });
    }

    /**
     * Service informatique plus the services the agent is attached to, as in the legacy flow.
     *
     * @return list<string>
     */
    private static function copies(Profile $profile): array
    {
        $copies = [];

        if ($informatique = config('agent.informatique_email')) {
            $copies[] = $informatique;
        }

        $employee = $profile->employee_id !== null
            ? Employee::query()->with('activeContracts.service')->find($profile->employee_id)
            : null;

        if ($employee instanceof Employee) {
            foreach ($employee->activeContracts as $contract) {
                if ($contract->service?->email) {
                    $copies[] = $contract->service->email;
                }
            }
        }

        return array_values(array_unique(array_diff($copies, [$profile->user?->email])));
    }
}
