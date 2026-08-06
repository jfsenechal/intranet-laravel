<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Actions;

use AcMarche\Agent\Enums\RolesEnum;
use AcMarche\Agent\Mail\ShareProfileMail;
use AcMarche\Agent\Models\Profile;
use AcMarche\Agent\Models\Share;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

final class ShareProfileAction
{
    public static function make(): Action
    {
        return Action::make('shareProfile')
            ->label('Déléguer')
            ->icon(Heroicon::UserPlus)
            ->color('info')
            ->visible(fn (): bool => Auth::user()?->can('update', Profile::class) === true)
            ->modalHeading(fn (Profile $record): string => 'Déléguer le profil de '.$record->fullName())
            ->modalDescription('Envoyer la fiche pour que le profil soit complété par quelqu\'un d\'autre.')
            ->modalSubmitActionLabel('Envoyer')
            ->schema([
                Select::make('email')
                    ->label('Destinataire')
                    ->helperText('Le rôle agent est attribué au destinataire s\'il ne l\'a pas encore.')
                    ->options(self::recipients(...))
                    ->searchable()
                    ->required(),
                Textarea::make('notes')
                    ->label('Remarques')
                    ->rows(5),
            ])
            ->action(function (array $data, Profile $record): void {
                $email = $data['email'];

                self::grantAgentRole($email);

                Share::query()->firstOrCreate([
                    'profile_id' => $record->getKey(),
                    'shared_for' => $email,
                ], [
                    'shared_by' => Auth::user()?->username ?? 'system',
                ]);

                $mail = Mail::to($email);

                if ($sender = Auth::user()?->email) {
                    $mail->cc($sender);
                }

                $mail->send(new ShareProfileMail($record, $data['notes'] ?? null));

                Notification::make()
                    ->title('Demande envoyée')
                    ->success()
                    ->send();
            });
    }

    /**
     * Every user reachable by mail, keyed by email address.
     *
     * @return array<string, string>
     */
    private static function recipients(): array
    {
        return User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->email => mb_trim($user->fullNameAsString()).' ('.$user->email.')',
            ])
            ->all();
    }

    /**
     * The delegate needs the agent role to reach the profile, as in the legacy flow.
     */
    private static function grantAgentRole(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User || $user->hasOneOfThisRoles([
            RolesEnum::ROLE_AGENT->value,
            RolesEnum::ROLE_AGENT_ADMIN->value,
        ])) {
            return;
        }

        $role = Role::query()->where('name', RolesEnum::ROLE_AGENT->value)->first();

        if (! $role instanceof Role) {
            Notification::make()
                ->title('Rôle agent introuvable')
                ->body('Le rôle '.RolesEnum::ROLE_AGENT->value.' n\'existe pas, le destinataire n\'a pas pu être autorisé.')
                ->warning()
                ->send();

            return;
        }

        $user->addRole($role);
    }
}
