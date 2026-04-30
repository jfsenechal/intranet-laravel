<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Services\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Intitulé'),
                        TextEntry::make('abbreviation')
                            ->label('Abréviation'),
                        TextEntry::make('direction.name')
                            ->label('Direction'),
                        TextEntry::make('employer.name')
                            ->label('Employeur'),
                    ]),
                Section::make('Coordonnées')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('address')
                            ->label('Adresse')
                            ->columnSpanFull(),
                        TextEntry::make('postal_code')
                            ->label('Code postal'),
                        TextEntry::make('city')
                            ->label('Ville'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('phone')
                            ->label('Téléphone')
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('gsm')
                            ->label('GSM')
                            ->icon('heroicon-o-device-phone-mobile'),
                    ]),
                Section::make('Remarques')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Remarques')
                            ->html()
                            ->prose()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
