<?php

namespace AcMarche\Mileage\Filament\Resources\Declarations\Schemas;

use AcMarche\Mileage\Dto\DeclarationSummary;
use AcMarche\Mileage\Handler\Calculator;
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
                    ->icon('tabler-cookie-man')
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
                    ->icon('tabler-currency-euro')
                    ->schema([
                        Flex::make([
                            TextEntry::make('rate')
                                ->label('Tarif (€/km)')
                                ->money('EUR'),
                            TextEntry::make('rate_omnium')
                                ->label('Tarif omnium (€/km)')
                                ->money('EUR')
                                ->visible(fn ($record): bool => $record->omnium),
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
                    ->icon('tabler-car')
                    ->schema([
                        Flex::make([
                            TextEntry::make('car_license_plate1')
                                ->label('Plaque 1'),
                            TextEntry::make('car_license_plate2')
                                ->label('Plaque 2'),
                            TextEntry::make('omnium')
                                ->label('Omnium')
                                ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non'),
                        ]),
                    ])->grow(false),
                Section::make('Résumé des frais')
                    ->schema([
                        Flex::make([
                            TextEntry::make('totalKilometers')
                                ->label('Total kilomètres')
                                ->state(fn (Declaration $record): int => self::getCalculator($record)->totalKilometers)
                                ->suffix(' km')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('totalMileageAllowance')
                                ->label('Indemnité kilométrique')
                                ->state(fn (Declaration $record): float => self::getCalculator($record)->totalMileageAllowance)
                                ->money('EUR'),
                        ])->grow(false),
                        Flex::make([
                            TextEntry::make('totalOmnium')
                                ->label('Retenue omnium')
                                ->state(fn (Declaration $record): float => self::getCalculator($record)->totalOmnium)
                                ->money('EUR')
                                ->visible(fn ($record): bool => $record->omnium),
                            TextEntry::make('mealExpense')
                                ->label('Frais de repas')
                                ->state(fn (Declaration $record): float => self::getCalculator($record)->mealExpense)
                                ->money('EUR')
                                ->visible(fn (Declaration $record): bool => self::getCalculator($record)->mealExpense > 0),
                            TextEntry::make('trainExpense')
                                ->label('Frais de train')
                                ->state(fn (Declaration $record): float => self::getCalculator($record)->trainExpense)
                                ->money('EUR')
                                ->visible(fn (Declaration $record): bool => self::getCalculator($record)->trainExpense > 0),
                        ])->grow(false),
                        Flex::make([
                            TextEntry::make('totalExpense')
                                ->label('Total frais annexes')
                                ->state(fn (Declaration $record): float => self::getCalculator($record)->totalExpense)
                                ->money('EUR')
                                ->visible(fn (Declaration $record): bool => self::getCalculator($record)->totalExpense > 0),
                            TextEntry::make('totalRefund')
                                ->label('Total à rembourser')
                                ->state(fn (Declaration $record): float => self::getCalculator($record)->totalRefund)
                                ->money('EUR')
                                ->weight(FontWeight::Bold)
                                ->color('success'),
                        ])->grow(false),
                    ])
                    ->icon('tabler-calculator')
                    ->collapsible(),
            ]);
    }

    private static function getCalculator(Declaration $record): DeclarationSummary
    {
        static $cache = [];

        if (! isset($cache[$record->id])) {
            $record->loadMissing('trips');
            $calculator = new Calculator($record);
            $cache[$record->id] = $calculator->calculate();
        }

        return $cache[$record->id];
    }
}
