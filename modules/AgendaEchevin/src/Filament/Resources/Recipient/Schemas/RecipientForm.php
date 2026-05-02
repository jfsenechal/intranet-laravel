<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Recipient\Schemas;

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
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('token')
                    ->label('Token')
                    ->required()
                    ->maxLength(255),
                Toggle::make('ics')
                    ->label('ICS')
                    ->default(true),
            ]);
    }
}
