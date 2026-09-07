<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ResidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Résident')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('last_name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('first_name')
                                    ->label('Prénom')
                                    ->maxLength(100),

                                TextInput::make('room')
                                    ->label('Chambre')
                                    ->maxLength(20),

                                Toggle::make('is_active')
                                    ->label('Actif')
                                    ->helperText('Un résident inactif ne peut plus recevoir de réservation.')
                                    ->default(true),
                            ]),

                        Textarea::make('notes')
                            ->label('Remarques')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
