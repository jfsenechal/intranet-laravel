<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Recipient\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class RecipientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextInput::make('last_name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('first_name')
                    ->label('Prénom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(150),
                Toggle::make('ics')
                    ->label('ICS')
                    ->helperText('Joindre au mail')
                    ->default(true),
            ]);
    }
}
