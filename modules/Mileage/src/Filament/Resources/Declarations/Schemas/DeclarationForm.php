<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Schemas;

use AcMarche\Mileage\Models\BudgetArticle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DeclarationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('street')
                            ->label('Rue')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('postal_code')
                            ->label('Code postal')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('Localité')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('iban')
                            ->label('IBAN')
                            ->required()
                            ->rule('iban')
                            ->placeholder('BE00 0000 0000 0000')
                            ->helperText('Format: BE00 0000 0000 0000'),
                    ])
                    ->columns(2),

                Section::make('Véhicule')
                    ->schema([
                        TextInput::make('car_license_plate1')
                            ->label('Plaque 1')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('car_license_plate2')
                            ->label('Plaque 2')
                            ->maxLength(255),

                        Toggle::make('omnium')
                            ->label('Omnium')
                            ->default(false),
                    ])
                    ->columns(3),

                Section::make('Tarifs et classification')
                    ->schema([
                        TextInput::make('rate')
                            ->label('Tarif (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        TextInput::make('rate_omnium')
                            ->label('Tarif omnium (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        TextInput::make('type_movement')
                            ->label('Type de déplacement')
                            ->required()
                            ->maxLength(255),

                        Select::make('budget_article')
                            ->label('Article budgétaire')
                            ->required()
                            ->options(BudgetArticle::query()->pluck('name', 'name'))
                            ->searchable(),

                        TextInput::make('departments')
                            ->label('Départements')
                            ->maxLength(255),

                        DatePicker::make('college_date')
                            ->label('Date collège'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function editFormForAdmin(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make([
                    Select::make('budget_article')
                        ->label('Article budgétaire')
                        ->required()
                        ->options(BudgetArticle::query()->pluck('name', 'name'))
                        ->searchable(),
                    TextInput::make('iban')
                        ->label('IBAN')
                        ->required()
                        ->rule('iban')
                        ->placeholder('BE00 0000 0000 0000')
                        ->helperText('Format: BE00 0000 0000 0000'),
                ])->columnSpanFull(),
                Flex::make([
                    TextInput::make('car_license_plate1')
                        ->label('Plaque 1')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('car_license_plate2')
                        ->label('Plaque 2')
                        ->maxLength(255),
                ])->columnSpanFull(),
            ]);
    }
}
