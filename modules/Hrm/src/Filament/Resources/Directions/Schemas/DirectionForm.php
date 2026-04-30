<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Directions\Schemas;

use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DirectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Intitule')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true),
                        TextInput::make('abbreviation')
                            ->label('Abreviation')
                            ->maxLength(255),
                        Select::make('director')
                            ->label('Directeur')
                            ->options(UserRepository::listLdapUsersForSelect())
                            ->searchable(),
                        Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
