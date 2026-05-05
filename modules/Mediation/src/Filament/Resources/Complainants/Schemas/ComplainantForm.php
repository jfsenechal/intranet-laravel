<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ComplainantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identité')
                    ->schema([
                        Select::make('salutation')
                            ->label('Civilité')
                            ->options([
                                'M.' => 'M.',
                                'Mme' => 'Mme',
                                'Dr' => 'Dr',
                            ])
                            ->nullable(),

                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->maxLength(255),

                        DatePicker::make('birth_date')
                            ->label('Date de naissance'),
                    ])
                    ->columns(2),

                Section::make('Adresse')
                    ->schema([
                        TextInput::make('street')
                            ->label('Rue')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('postal_code')
                            ->label('Code postal')
                            ->required()
                            ->maxLength(20),

                        TextInput::make('city')
                            ->label('Localité')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(3),
            ]);
    }
}
