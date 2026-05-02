<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('week_id')
                                    ->label('Week')
                                    ->relationship('week', 'first_day')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'last_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Toggle::make('is_last_meal')
                            ->label('Last meal (close client)')
                            ->default(false),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
