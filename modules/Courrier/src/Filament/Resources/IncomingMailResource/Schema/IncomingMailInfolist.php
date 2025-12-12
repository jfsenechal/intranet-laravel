<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMailResource\Schema;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class IncomingMailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('reference')
                            ->label('Référence')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('received_date')
                            ->label('Date de réception')
                            ->date('d/m/Y'),
                        TextEntry::make('sender_name')
                            ->label('Expéditeur'),
                        TextEntry::make('sender_address')
                            ->label('Adresse de l\'expéditeur')
                            ->columnSpanFull()
                            ->hidden(fn ($state): bool => blank($state)),
                        TextEntry::make('subject')
                            ->label('Objet')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull()
                            ->prose()
                            ->hidden(fn ($state): bool => blank($state)),
                    ])
                    ->columns(2),
                Section::make('Traitement')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'En attente',
                                'processed' => 'Traité',
                                'archived' => 'Archivé',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'processed' => 'success',
                                'archived' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('assigned_to')
                            ->label('Assigné à')
                            ->hidden(fn ($state): bool => blank($state)),
                        TextEntry::make('processed_date')
                            ->label('Date de traitement')
                            ->date('d/m/Y')
                            ->hidden(fn ($state): bool => blank($state)),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull()
                            ->hidden(fn ($state): bool => blank($state)),
                    ])
                    ->columns(2),
                Section::make('Pièce jointe')
                    ->schema([
                        Flex::make([
                            TextEntry::make('attachment_name')
                                ->label('Nom du fichier')
                                ->hidden(fn ($state): bool => blank($state)),
                            TextEntry::make('attachment_size')
                                ->label('Taille')
                                ->formatStateUsing(fn ($state): string => $state ? number_format($state / 1024, 2).' KB' : '-')
                                ->hidden(fn ($state): bool => blank($state)),
                            TextEntry::make('attachment_mime')
                                ->label('Type')
                                ->hidden(fn ($state): bool => blank($state)),
                        ]),
                    ])
                    ->hidden(fn ($record): bool => blank($record->attachment_path)),
            ]);
    }
}
