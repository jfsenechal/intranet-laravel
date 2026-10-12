<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Contacts\Schemas;

use AcMarche\MailingList\Models\Contact;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coordonnées')
                    ->schema([
                        TextEntry::make('last_name')
                            ->label('Nom')
                            ->placeholder('-'),
                        TextEntry::make('first_name')
                            ->label('Prénom')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Téléphone')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Informations')
                    ->schema([
                        TextEntry::make('addressBooks.name')
                            ->label('Carnets d\'adresses')
                            ->badge()
                            ->placeholder('Aucun'),
                        TextEntry::make('unsubscribed_at')
                            ->label('Désinscrit le')
                            ->dateTime()
                            ->placeholder('Abonné'),
                        TextEntry::make('username')
                            ->label('Propriétaire')
                            ->visible(fn (Contact $record): bool => $record->username !== auth()->user()?->username),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
