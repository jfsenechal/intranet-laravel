<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EmployeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns()
                    ->components([
                        TextInput::make('givenName')
                            ->label('Prénom')
                            ->maxLength(64),
                        TextInput::make('sn')
                            ->label('Nom')
                            ->required()
                            ->maxLength(64),
                        TextInput::make('samaccountname')
                            ->label('Identifiant')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText("L'identifiant ne peut pas être modifié après la création."),
                        TextInput::make('mail')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                self::contact(),
                Section::make('Divers')
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function contact(): Section
    {
        return Section::make('Coordonnées')
            ->columns()
            ->components([
                TextInput::make('telephoneNumber')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(64),
            ]);
    }
}
