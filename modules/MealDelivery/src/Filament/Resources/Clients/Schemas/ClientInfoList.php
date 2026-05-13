<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Adresse')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('street')
                                                    ->label('Rue')
                                                    ->columnSpan(2),

                                                TextEntry::make('number')
                                                    ->label('Numéro'),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('postal_code')
                                                    ->label('Code postal'),

                                                TextEntry::make('city')
                                                    ->label('Localité')
                                                    ->columnSpan(2),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('birth_date')
                                                    ->label('Né le')
                                                    ->date()
                                                    ->placeholder('—'),

                                                TextEntry::make('floor')
                                                    ->label('Etage')
                                                    ->placeholder('—'),
                                            ]),
                                    ]),

                                Section::make('Contact')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('email')
                                            ->label('Email')
                                            ->placeholder('—')
                                            ->copyable(),

                                        TextEntry::make('phone')
                                            ->label('Téléphone')
                                            ->placeholder('—')
                                            ->copyable(),

                                        TextEntry::make('contact_name')
                                            ->label('Personne de contact')
                                            ->placeholder('—'),

                                        TextEntry::make('contact_phone')
                                            ->label('Téléphone du contact')
                                            ->placeholder('—')
                                            ->copyable(),

                                        TextEntry::make('contact_notes')
                                            ->label('Contact remarque')
                                            ->placeholder('—')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Notes')
                                    ->schema([
                                        TextEntry::make('notes')
                                            ->label('Notes')
                                            ->hiddenLabel()
                                            ->placeholder('—')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Absence')
                                    ->columns(2)
                                    ->visible(fn ($record): bool => $record->absence !== null)
                                    ->schema([
                                        TextEntry::make('absence.start_date')
                                            ->label('Du')
                                            ->date()
                                            ->placeholder('—'),

                                        TextEntry::make('absence.end_date')
                                            ->label('Au')
                                            ->date()
                                            ->placeholder('—'),
                                    ]),

                            ]),

                        Grid::make(2)
                            ->schema([
                                Section::make('Options')
                                    ->columnSpanFull()
                                    ->schema([
                                        IconEntry::make('is_active')
                                            ->label('Active')
                                            ->boolean(),

                                        IconEntry::make('use_cafeteria')
                                            ->label('Mange à la cafétéria')
                                            ->boolean(),
                                    ]),
                                Section::make('Paramètres de la tournée')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('deliveryRoute.name')
                                            ->label('Tournée')
                                            ->placeholder('—'),

                                        TextEntry::make('recurring_order')
                                            ->label('Commande récurrente')
                                            ->placeholder('—'),

                                        TextEntry::make('diets.name')
                                            ->label('Régimes')
                                            ->badge()
                                            ->placeholder('—'),
                                    ]),

                            ]),

                    ]),
            ]);
    }
}
