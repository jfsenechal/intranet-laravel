<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Schemas;

use Carbon\CarbonImmutable;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Hidden::make('week_id'),
                Hidden::make('client_id'),

                Repeater::make('meals')
                    ->hiddenLabel()
                    ->itemLabel(fn (array $state): ?string => isset($state['date'])
                        ? CarbonImmutable::parse($state['date'])->translatedFormat('l j F')
                        : null,
                    )
                    ->schema([
                        Hidden::make('date'),

                        TextInput::make('soup_count')
                            ->label('Potage')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('menu_1')
                            ->label('Menu 1')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('menu_2')
                            ->label('Menu 2')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Textarea::make('notes')
                            ->label('Remarque')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->grid(['default' => 1, 'md' => 2, 'lg' => 4, '2xl' => 7])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->collapsible(false)
                    ->defaultItems(0)
                    ->columnSpanFull(),

                Section::make()
                    ->schema([
                        Toggle::make('is_last_meal')
                            ->label('Dernière commande ?')
                            ->helperText('Si oui, prendre feuille nouvelle commande')
                            ->default(false),

                        Textarea::make('notes')
                            ->label('Remarque générale')
                            ->rows(2)
                            ->nullable(),
                    ]),
            ]);
    }
}
