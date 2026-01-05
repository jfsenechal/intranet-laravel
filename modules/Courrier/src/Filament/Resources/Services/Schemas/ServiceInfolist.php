<?php

namespace AcMarche\Courrier\Filament\Resources\Services\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations du service')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                        TextEntry::make('initials')
                            ->label('Initiales'),
                        IconEntry::make('is_active')
                            ->label('Actif')
                            ->boolean(),
                    ])
                    ->columns(3),

                Section::make('Destinataires')
                    ->description('Liste des membres affectés à ce service')
                    ->schema([
                        RepeatableEntry::make('recipients')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label('Nom'),
                                TextEntry::make('email')
                                    ->label('Email'),
                                IconEntry::make('is_active')
                                    ->label('Actif')
                                    ->boolean(),
                            ])
                            ->columns(3)
                            ->contained(false),
                    ])
                    ->hidden(fn ($record): bool => $record->recipients->isEmpty()),
            ]);
    }
}
