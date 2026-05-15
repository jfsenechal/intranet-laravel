<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activites\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ActiviteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->schema([
                        TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('archive')
                            ->label('Archivée')
                            ->default(false),
                    ]),
            ]);
    }
}
