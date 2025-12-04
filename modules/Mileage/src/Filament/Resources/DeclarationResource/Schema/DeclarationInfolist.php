<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Schema;

use AcMarche\Mileage\Models\Declaration;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

final class DeclarationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        Flex::make([
                            TextEntry::make('first_name')
                                ->weight(FontWeight::Bold)
                                ->label('Prénom'),
                            TextEntry::make('last_name')
                                ->label('Nom'),
                        ])->grow(false),
                        Flex::make([
                            TextEntry::make('street')
                                ->label('Rue'),
                            TextEntry::make('city')
                                ->label('Localité'),
                        ])->grow(false),
                        Flex::make([
                            TextEntry::make('postal_code')
                                ->label('Code postal'),
                            TextEntry::make('iban')
                                ->label('Iban'),
                        ])->grow(false),
                    ]),
                Section::make('Tarifs et classification')
                    ->schema([
                        Flex::make([
                            TextEntry::make('rate')
                                ->label('Tarif (€/km)')
                                ->money('EUR'),
                            TextEntry::make('rate_omnium')
                                ->label('Tarif omnium (€/km)')
                                ->money('EUR')
                                ->visible(fn($record): bool => $record->omnium),
                        ])->grow(false),
                        Flex::make([
                            TextEntry::make('budget_article')
                                ->label('Article budgétaire'),
                            TextEntry::make('college_date')
                                ->label('Date de Collège')
                                ->date(),
                        ])->grow(false),
                    ]),
                Section::make('Véhicule')
                    ->schema([
                        Flex::make([
                            TextEntry::make('car_license_plate1')
                                ->label('Plaque 1'),
                            TextEntry::make('car_license_plate2')
                                ->label('Plaque 2'),
                            TextEntry::make('omnium')
                                ->label('Omnium')
                                ->formatStateUsing(fn(bool $state): string => 'oui' ?? 'non'),
                        ]),
                    ])->grow(false),
            ]);
    }
}
