<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('last_name')->label('Nom')->required()->maxLength(255),
                            TextInput::make('first_name')->label('Prénom')->required()->maxLength(255),
                            DatePicker::make('birth_date')->label('Date de naissance')->native(false),
                            TextInput::make('user')->label('Créé par')->required()->maxLength(255),
                        ]),
                    ]),

                Section::make('Adresse')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('street')->label('Rue')->required()->maxLength(255)->columnSpanFull(),
                            TextInput::make('postal_code')->label('Code postal')->required()->maxLength(255),
                            TextInput::make('city')->label('Localité')->required()->maxLength(255),
                        ]),
                    ]),

                Section::make('Contact')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('phone')->label('Téléphone')->tel()->maxLength(255),
                            TextInput::make('mobile')->label('GSM')->tel()->maxLength(255),
                            TextInput::make('email')->label('Email')->email()->maxLength(255)->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Remarque')
                    ->schema([
                        Textarea::make('comment')->label('Remarque')->rows(3),
                    ]),
            ]);
    }
}
