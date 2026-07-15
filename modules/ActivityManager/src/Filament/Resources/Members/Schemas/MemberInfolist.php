<?php

namespace AcMarche\ActivityManager\Filament\Resources\Members\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('civility')
                            ->label('Civilité')->badge()
                            ->placeholder('—'),
                        IconEntry::make('enabled')
                            ->label('Actif')->boolean(),
                        TextEntry::make('registered_at')
                            ->label('Inscrit le')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                    ]),

                Section::make('Adresse')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('street')
                            ->label('Rue')
                            ->placeholder('—'),
                        TextEntry::make('number')
                            ->label('N°')
                            ->placeholder('—'),
                        TextEntry::make('postal_code')
                            ->label('Code postal')
                            ->placeholder('—'),
                        TextEntry::make('city')
                            ->label('Localité')
                            ->placeholder('—'),
                    ]),

                Section::make('Contact')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('mobile')->label('GSM')->copyable()->placeholder('—'),
                        TextEntry::make('phone')->label('Téléphone')->copyable()->placeholder('—'),
                        TextEntry::make('email')->label('Email')->copyable()->placeholder('—'),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('remark')->label('Remarque')->columnSpanFull()->placeholder('—'),
                    ]),
            ]);
    }
}
