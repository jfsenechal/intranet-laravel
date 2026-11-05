<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Models\Citoyen;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class ChangePasswordAction
{
    public static function make(Model|Citoyen $record): Action
    {
        return Action::make('changePassword')
            ->label('Changer le mot de passe')
            ->icon(Heroicon::Key)
            ->color('warning')
            ->authorize('update', $record)
            ->modalHeading("Mot de passe de {$record->mail}")
            ->modalSubmitActionLabel('Enregistrer')
            ->schema([
                TextInput::make('password')
                    ->label('Nouveau mot de passe')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->required()
                    ->rule(Password::defaults())
                    ->afterContent(
                        Action::make('generatePassword')
                            ->label('Générer')
                            ->icon(Heroicon::Sparkles)
                            ->color('gray')
                            ->action(function (Set $schemaSet): void {
                                $schemaSet('password', self::generatePassword());
                            }),
                    ),
            ])
            ->action(function (array $data) use ($record): void {
                $record->virtualUser->changePassword($data['password']);

                Notification::make()
                    ->title('Le mot de passe a bien été modifié')
                    ->success()
                    ->send();
            });
    }

    /**
     * Str::password() guarantees a letter and a number but not both cases, which
     * Password::defaults() requires.
     */
    private static function generatePassword(): string
    {
        do {
            $password = Str::password(12, symbols: false);
        } while (mb_strtolower($password) === $password || mb_strtoupper($password) === $password);

        return $password;
    }
}
