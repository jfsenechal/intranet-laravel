<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groups\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

final class GroupForm
{
    public static function schema(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('day')
                    ->label('Jour')
                    ->helperText('Lundi, mardi...')
                    ->required()
                    ->maxLength(255),
                TextInput::make('time')
                    ->label('Heure')
                    ->helperText('18h30, 19h30...')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('Lieu')
                    ->helperText('Piscine, Saint François, Ccs...')
                    ->required()
                    ->maxLength(255),
                TextInput::make('age')
                    ->label('Âge')
                    ->helperText('3 à 10 ans, 9 - 12 ans...')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Prix')
                    ->numeric()
                    ->required()
                    ->default(0),
            ]),
            Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }
}
