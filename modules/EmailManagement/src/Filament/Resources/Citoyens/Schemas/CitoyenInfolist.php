<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The token hashes (auth_token, remember_token) are deliberately absent: they are hidden on
 * the model and must never be rendered.
 */
final class CitoyenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Grid::make(1)
                        ->schema([
                            Section::make('Identité')
                                ->columns(3)
                                ->components([
                                    TextEntry::make('givenName')
                                        ->label('Prénom')
                                        ->placeholder('-'),
                                    TextEntry::make('sn')
                                        ->label('Nom')
                                        ->placeholder('-'),
                                    TextEntry::make('employeeNumber')
                                        ->label('Numéro national')
                                        ->placeholder('-'),
                                ]),
                            Section::make('Messagerie')
                                ->columns(2)
                                ->components([
                                    TextEntry::make('virtualUser.quota_bytes')
                                        ->label('Quota')
                                        ->numeric()
                                        ->suffix(' Mo')
                                        ->placeholder('-'),
                                    TextEntry::make('homeDirectory')
                                        ->label('Répertoire'),
                                ]),
                            Section::make('Adresse')
                                ->columns(3)
                                ->components([
                                    TextEntry::make('postalAddress')
                                        ->label('Rue')
                                        ->placeholder('-')                                        ,
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
                                    TextEntry::make('charter_accepted_at')
                                        ->label('Charte acceptée le')
                                        ->dateTime()
                                        ->placeholder('Non acceptée'),
                                ]),

                                    TextEntry::make('description')
                                        ->label('Description')
                                        ->placeholder('-')
                                        ->columnSpanFull(),

                        ]),
                    Section::make('État')
                        ->components([
                            IconEntry::make('virtualUser.active')
                                ->label('Actif')
                                ->boolean()
                                ->placeholder('-'),
                            TextEntry::make('created_at')
                                ->label('Créé le')
                                ->dateTime(),
                            TextEntry::make('updated_at')
                                ->label('Modifié le')
                                ->dateTime(),
                        ])
                        ->grow(false),
                ])->from('md')
                    ->columnSpanFull(),
            ]);
    }
}
