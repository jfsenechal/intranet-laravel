<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Schemas;

use AcMarche\MealDelivery\Filament\Resources\Meals\Schemas\MealForm;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                        ? Str::title(CarbonImmutable::parse($state['date'])->translatedFormat('l j F'))
                        : null,
                    )
                    ->schema(
                        MealForm::getMealSchema(),
                    )
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
