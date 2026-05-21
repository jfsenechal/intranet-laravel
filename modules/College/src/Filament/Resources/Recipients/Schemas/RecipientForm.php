<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RecipientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->schema([
                        Grid::make(2)->schema([
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
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Convocations')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('pv_service')
                                ->label('PV Service')
                                ->default(false),
                            Toggle::make('ordre_service')
                                ->label('Ordre du jour - Service')
                                ->default(false),
                            Toggle::make('ordre_college')
                                ->label('Ordre du jour - Collège')
                                ->default(false),
                            Toggle::make('pv_college')
                                ->label('PV Collège')
                                ->default(false),
                        ]),
                    ]),
            ]);
    }
}
