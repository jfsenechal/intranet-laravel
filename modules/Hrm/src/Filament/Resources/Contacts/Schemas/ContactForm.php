<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('last_name')
                    ->label('Nom')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('first_name')
                    ->label('Prénom')
                    ->maxLength(255),
                TextInput::make('email_1')
                    ->label('Email 1')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone_1')
                    ->label('Téléphone 1')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('email_2')
                    ->label('Email 2')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone_2')
                    ->label('Téléphone 2')
                    ->tel()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3),
            ]);
    }
}
