<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class PasswordInput
{
    /**
     * The password fields for a staff account.
     *
     * The value is written to Active Directory through unicodePwd and is never stored
     * locally, so there is no hashing here.
     *
     * @return array<int, TextInput>
     */
    public static function create(): array
    {
        return [
            TextInput::make('password')
                ->label('Mot de passe')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->rule(Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised())
                ->confirmed()
                ->helperText('12 caractères minimum, avec majuscules, minuscules, chiffres et symboles.')
                ->afterContent(
                    Action::make('generatePassword')
                        ->label('Générer')
                        ->icon(Heroicon::Sparkles)
                        ->color('gray')
                        ->action(function (Set $schemaSet): void {
                            $password = Str::password(16);

                            $schemaSet('password', $password);
                            $schemaSet('password_confirmation', $password);
                        }),
                ),
            TextInput::make('password_confirmation')
                ->label('Confirmer le mot de passe')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                // Confirmation-only: must never reach the directory or the mirror.
                ->dehydrated(false),
        ];
    }
}
