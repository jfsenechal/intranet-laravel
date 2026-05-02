<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                                Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'last_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                DatePicker::make('note_date')
                                    ->label('Date')
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
                                    ->label('Done')
                                    ->default(false)
                                    ->live()
                                    ->columnSpan(1),

                                TextInput::make('done_by')
                                    ->label('Done by')
                                    ->maxLength(100)
                                    ->nullable()
                                    ->visible(fn ($get) => (bool) $get('is_done'))
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }
}
