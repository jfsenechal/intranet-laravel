<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OffenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identité')
                    ->schema([
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('birth_date')
                            ->label('Date de naissance')
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Adresse')
                    ->schema([
                        TextInput::make('street')
                            ->label('Rue')
                            ->maxLength(255),

                        TextInput::make('postal_code')
                            ->label('Code postal')
                            ->maxLength(20),

                        TextInput::make('city')
                            ->label('Localité')
                            ->maxLength(255),
                    ])
                    ->columns(3),
            ]);
    }
}
