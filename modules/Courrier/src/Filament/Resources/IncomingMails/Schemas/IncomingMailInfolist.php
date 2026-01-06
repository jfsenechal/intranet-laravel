<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
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
                Section::make('Informations du courrier')
                    ->schema([
                        TextEntry::make('reference_number')
                            ->label('Numéro de référence')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('mail_date')
                            ->label('Date du courrier')
                            ->date('d/m/Y'),
                        TextEntry::make('sender')
                            ->label('Expéditeur'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull()
                            ->prose()
                            ->hidden(fn ($state): bool => blank($state)),
                    ])
                    ->columns(2),

                Section::make('Options')
                    ->schema([
                        Flex::make([
                            IconEntry::make('is_notified')
                                ->label('Notifié')
                                ->boolean(),
                            IconEntry::make('is_registered')
                                ->label('Recommandé')
                                ->boolean(),
                            IconEntry::make('has_acknowledgment')
                                ->label('Accusé de réception')
                                ->boolean(),
                        ]),
                    ]),

                Section::make('Affectation')
                    ->schema([
                        TextEntry::make('services.name')
                            ->label('Services')
                            ->badge()
                            ->separator(',')
                            ->hidden(fn ($state): bool => blank($state)),
                        TextEntry::make('recipients.full_name')
                            ->label('Destinataires')
                            ->badge()
                            ->color('gray')
                            ->separator(',')
                            ->hidden(fn ($state): bool => blank($state)),
                    ])
                    ->columns(2),

                Section::make('Pièces jointes')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        RepeatableEntry::make('attachments')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('file_name')
                                    ->label('Fichier')
                                    ->url(fn ($record) => route('courrier.attachments.download', $record))
                                    ->openUrlInNewTab()
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('primary'),
                                TextEntry::make('mime')
                                    ->label('Type')
                                    ->badge()
                                    ->color('gray'),
                            ])
                            ->columns(2)
                            ->contained(false),
                    ])
                    ->hidden(fn ($record): bool => $record->attachments->isEmpty()),

                Section::make('Métadonnées')
                    ->schema([
                        TextEntry::make('user_add')
                            ->label('Créé par'),
                        TextEntry::make('created_at')
                            ->label('Date de création')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Dernière modification')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }
}
