<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

final class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),

                Select::make('activity_id')
                    ->label('Activité')
                    ->relationship('activity', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->label('Nom')->required()->maxLength(150),
                        Textarea::make('description')->label('Description')->rows(3),
                    ])
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    DatePicker::make('start_date')
                        ->label('Date de début')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->native(false),
                    DatePicker::make('end_date')
                        ->label('Date de fin')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->afterOrEqual('start_date'),
                ]),
            ]);
    }
}
