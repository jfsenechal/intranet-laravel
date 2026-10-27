<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Only the fillable columns are fields here: the mailbox identity (mail, uid, dn, quota,
 * home directory) and the password and token hashes are left out of the schema entirely,
 * so a save can never submit them.
 */
final class CitoyenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns()
                    ->components([
                        TextInput::make('givenName')
                            ->label('Prénom')
                            ->maxLength(255),
                        TextInput::make('sn')
                            ->label('Nom')
                            ->maxLength(255),
                        TextInput::make('cn')
                            ->label('Nom complet')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Section::make('Adresse')
                    ->columns()
                    ->components([
                        TextInput::make('postalAddress')
                            ->label('Rue')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('postalCode')
                            ->label('Code postal')
                            ->maxLength(255),
                        TextInput::make('l')
                            ->label('Localité')
                            ->maxLength(255),
                    ]),
                Section::make('Récupération du compte')
                    ->columns()
                    ->components([
                        TextInput::make('recovery_email')
                            ->label('Email de récupération')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('recovery_phone')
                            ->label('Téléphone de récupération')
                            ->tel()
                            ->maxLength(255),
                    ]),
                Section::make('Divers')
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
