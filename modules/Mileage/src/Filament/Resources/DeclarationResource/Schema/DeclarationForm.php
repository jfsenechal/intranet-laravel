<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Schema;

use AcMarche\Mileage\Models\BudgetArticle;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeclarationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('prenom')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('rue')
                            ->label('Rue')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code_postal')
                            ->label('Code postal')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('localite')
                            ->label('Localité')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('iban')
                            ->label('IBAN')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Véhicule')
                    ->schema([
                        Forms\Components\TextInput::make('plaque1')
                            ->label('Plaque 1')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('plaque2')
                            ->label('Plaque 2')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('omnium')
                            ->label('Omnium')
                            ->default(false),
                    ])
                    ->columns(3),

                Section::make('Tarifs et classification')
                    ->schema([
                        Forms\Components\TextInput::make('tarif')
                            ->label('Tarif (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('tarif_omnium')
                            ->label('Tarif omnium (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('type_deplacement')
                            ->label('Type de déplacement')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('article_budgetaire')
                            ->label('Article budgétaire')
                            ->required()
                            ->options(BudgetArticle::query()->pluck('nom', 'nom'))
                            ->searchable(),

                        Forms\Components\TextInput::make('departments')
                            ->label('Départements')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('date_college')
                            ->label('Date collège'),
                    ])
                    ->columns(2),
            ]);
    }
}
