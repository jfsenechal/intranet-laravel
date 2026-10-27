<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The password and token hashes (userPassword, legacy_password, auth_token, remember_token)
 * are deliberately absent: they are hidden on the model and must never be rendered.
 */
final class CitoyenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns()
                    ->components([
                        TextEntry::make('givenName')
                            ->label('Prénom')
                            ->placeholder('-'),
                        TextEntry::make('sn')
                            ->label('Nom')
                            ->placeholder('-'),
                        TextEntry::make('cn')
                            ->label('Nom complet')
                            ->placeholder('-'),
                        TextEntry::make('uid')
                            ->label('Identifiant'),
                        TextEntry::make('employeeNumber')
                            ->label('Numéro national')
                            ->placeholder('-'),
                    ]),
                Section::make('Messagerie')
                    ->columns()
                    ->components([
                        TextEntry::make('mail')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('gosaMailForwardingAddress')
                            ->label('Adresse de transfert')
                            ->copyable(),
                        TextEntry::make('gosaMailAlternateAddress')
                            ->label('Adresse alternative')
                            ->placeholder('-'),
                        TextEntry::make('gosaMailQuota')
                            ->label('Quota')
                            ->numeric()
                            ->suffix(' Mo'),
                        TextEntry::make('homeDirectory')
                            ->label('Répertoire')
                            ->columnSpanFull(),
                    ]),
                Section::make('Adresse')
                    ->columns()
                    ->components([
                        TextEntry::make('postalAddress')
                            ->label('Rue')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('postalCode')
                            ->label('Code postal')
                            ->placeholder('-'),
                        TextEntry::make('l')
                            ->label('Localité')
                            ->placeholder('-'),
                    ]),
                Section::make('Récupération du compte')
                    ->columns()
                    ->components([
                        TextEntry::make('recovery_email')
                            ->label('Email de récupération')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('recovery_phone')
                            ->label('Téléphone de récupération')
                            ->placeholder('-'),
                    ]),
                Section::make('Connexion')
                    ->columns()
                    ->components([
                        TextEntry::make('last_connection')
                            ->label('Dernière connexion')
                            ->date()
                            ->placeholder('Jamais'),
                        TextEntry::make('password_changed_at')
                            ->label('Mot de passe changé le')
                            ->dateTime()
                            ->placeholder('Jamais'),
                        TextEntry::make('charter_accepted_at')
                            ->label('Charte acceptée le')
                            ->dateTime()
                            ->placeholder('Non acceptée'),
                    ]),
                Section::make('Divers')
                    ->components([
                        TextEntry::make('dn')
                            ->label('DN')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime(),
                    ]),
            ]);
    }
}
