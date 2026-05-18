<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Cours\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

final class CoursForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->label('Nom')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),

                Select::make('activite_id')
                    ->label('Activité')
                    ->relationship('activite', 'nom')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('nom')->label('Nom')->required()->maxLength(150),
                        Textarea::make('description')->label('Description')->rows(3),
                    ])
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    DatePicker::make('date_debut')
                        ->label('Date de début')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->native(false),
                    DatePicker::make('date_fin')
                        ->label('Date de fin')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->afterOrEqual('date_debut'),
                ]),
            ]);
    }
}
