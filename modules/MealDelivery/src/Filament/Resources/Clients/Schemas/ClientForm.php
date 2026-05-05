<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('salutation')
                                    ->label('Salutation')
                                    ->options([
                                        'M.' => 'M.',
                                        'Mme' => 'Mme',
                                        'Dr' => 'Dr',
                                    ])
                                    ->nullable()
                                    ->columnSpan(1),

                                TextInput::make('last_name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('first_name')
                                    ->label('Prénom')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),

                        DatePicker::make('birth_date')
                            ->label('Né le')
                            ->nullable(),
                    ]),

                Section::make('Adresse')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('street')
                                    ->label('Rue')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                TextInput::make('number')
                                    ->label('Numéro')
                                    ->required()
                                    ->maxLength(20)
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('postal_code')
                                    ->label('Code postal')
                                    ->required()
                                    ->numeric()
                                    ->columnSpan(1),

                                TextInput::make('city')
                                    ->label('Localité')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ]),

                        TextInput::make('floor')
                            ->label('Etage')
                            ->maxLength(100)
                            ->nullable(),
                    ]),

                Section::make('Contact')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),

                        TextInput::make('contact_name')
                            ->label('Personne de contact')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('contact_phone')
                            ->label('Téléphone du contact')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),

                        Textarea::make('contact_notes')
                            ->label('Contact remarque')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Paramètres de la tournée')
                    ->schema([
                        Select::make('route_id')
                            ->label('Tournée')
                            ->helperText('Préférence de la tournée')
                            ->relationship('deliveryRoute', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('diets')
                            ->label('Dietary requirements')
                            ->relationship('diets', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Textarea::make('recurring_order')
                            ->label('Commande recurrente')
                            ->helperText('Par ex. menu1 et 2 tous les mercredi sauf si poisson')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Status & Notes')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText(
                                'Si la personne ne commande plus, décochez cette case, elle ne sera plus proposée dans les futures commandes et dans les tournées'
                            )
                            ->default(true),

                        Toggle::make('use_cafeteria')
                            ->label('Mange à la cafétéria')
                            ->helperText('Permettra de le préciser ou pas sur les commandes')
                            ->default(false),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->helperText('Votre profession actuelle ou ancienne')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
