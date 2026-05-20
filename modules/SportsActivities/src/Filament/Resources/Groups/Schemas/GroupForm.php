<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Groupe')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('activity_id')
                                ->label('Activité')
                                ->relationship('activity', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('day')->label('Jour')->required()->maxLength(255),
                            TextInput::make('time')->label('Heure')->required()->maxLength(255),
                            TextInput::make('location')->label('Lieu')->required()->maxLength(255),
                            TextInput::make('age')->label('Âge')->required()->maxLength(255),
                            TextInput::make('price')->label('Prix')->numeric()->required()->default(0),
                            TextInput::make('user')->label('Créé par')->required()->maxLength(255),
                            Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
                            Textarea::make('comment')->label('Remarque')->rows(3)->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
