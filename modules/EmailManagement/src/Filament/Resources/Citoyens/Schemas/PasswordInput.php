<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class PasswordInput
{
    public static function make(string $label = 'Mot de passe'): TextInput
    {
        return TextInput::make('password')
            ->label($label)
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
            );
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
