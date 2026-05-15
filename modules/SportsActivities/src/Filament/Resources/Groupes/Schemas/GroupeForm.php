<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groupes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class GroupeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Groupe')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('activite_id')
                                ->label('Activité')
                                ->relationship('activite', 'nom')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('jour')->label('Jour')->required()->maxLength(255),
                            TextInput::make('heure')->label('Heure')->required()->maxLength(255),
                            TextInput::make('lieux')->label('Lieu')->required()->maxLength(255),
                            TextInput::make('age')->label('Âge')->required()->maxLength(255),
                            TextInput::make('prix')->label('Prix')->numeric()->required()->default(0),
                            TextInput::make('user')->label('Créé par')->required()->maxLength(255),
                            Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
                            Textarea::make('remarque')->label('Remarque')->rows(3)->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
