<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective\Schemas;

use AcMarche\Pst\Enums\ActionScopeEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class StrategicObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Identification')
                    ->icon('tabler-target')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->placeholder('Saisissez le nom de l\'objectif stratégique')
                                    ->prefixIcon('tabler-file-text')
                                    ->maxLength(255),
                                TextInput::make('position')
                                    ->label('Position')
                                    ->required()
                                    ->placeholder('Ordre d\'affichage')
                                    ->prefixIcon('tabler-list-numbers')
                                    ->numeric(),
                                ToggleButtons::make('scope')
                                    ->label('Volet')
                                    ->required()
                                    ->options(ActionScopeEnum::class)
                                    ->grouped(),
                            ]),
                    ]),
            ]);
    }
}
