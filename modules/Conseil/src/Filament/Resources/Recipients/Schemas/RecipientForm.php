<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Recipients\Schemas;

use Filament\Forms\Components\TextInput;
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
            ]);
    }
}
