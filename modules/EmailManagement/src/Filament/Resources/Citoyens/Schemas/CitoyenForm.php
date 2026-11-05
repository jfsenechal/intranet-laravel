<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas;

use AcMarche\EmailManagement\Models\Citoyen;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Only the fillable columns are fields here: the mailbox identity (mail, uid, home
 * directory) and the token hashes are left out of the schema entirely, so a save can never
 * submit them. The login section is hidden for a citoyen without a login, otherwise saving
 * would create a virtual_users row with no password.
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
                    ]),
                Section::make('Messagerie')
                    ->relationship('virtualUser')
                    ->visible(fn (?Citoyen $record): bool => $record?->virtualUser !== null)
                    ->components([
                        Toggle::make('active')
                            ->label('Actif'),
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
