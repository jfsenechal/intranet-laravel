<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Hidden::make('client_id'),
                                DatePicker::make('note_date')
                                    ->label('Ajouté le')
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_done')
                                    ->label('Traité')
                                    ->default(false)
                                    ->live()
                                    ->columnSpan(1),

                                TextInput::make('done_by')
                                    ->label('Traité par')
                                    ->maxLength(100)
                                    ->nullable()
                                    ->visible(fn($get) => (bool)$get('is_done'))
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }
}
