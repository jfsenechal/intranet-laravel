<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Sportifs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SportifForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nom')->label('Nom')->required()->maxLength(255),
                            TextInput::make('prenom')->label('Prénom')->required()->maxLength(255),
                            DatePicker::make('ne_le')->label('Date de naissance')->native(false),
                            TextInput::make('user')->label('Créé par')->required()->maxLength(255),
                        ]),
                    ]),

                Section::make('Adresse')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('rue')->label('Rue')->required()->maxLength(255)->columnSpanFull(),
                            TextInput::make('code_postal')->label('Code postal')->required()->maxLength(255),
                            TextInput::make('localite')->label('Localité')->required()->maxLength(255),
                        ]),
                    ]),

                Section::make('Contact')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('telephone')->label('Téléphone')->tel()->maxLength(255),
                            TextInput::make('gsm')->label('GSM')->tel()->maxLength(255),
                            TextInput::make('email')->label('Email')->email()->maxLength(255)->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Remarque')
                    ->schema([
                        Textarea::make('remarque')->label('Remarque')->rows(3),
                    ]),
            ]);
    }
}
