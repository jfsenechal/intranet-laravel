<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class MealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('order_id')
                                    ->label('Order')
                                    ->relationship('order', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                DatePicker::make('date')
                                    ->label('Date')
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('soup_count')
                                    ->label('Soup count')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->columnSpan(1),

                                Toggle::make('at_cafeteria')
                                    ->label('At cafeteria')
                                    ->default(false)
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),

                        Repeater::make('menus')
                            ->label('Menus')
                            ->relationship()
                            ->schema([
                                TextInput::make('position')
                                    ->label('Position')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),

                                Select::make('diets')
                                    ->label('Dietary requirements')
                                    ->relationship('diets', 'name')
                                    ->multiple()
                                    ->preload(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
