<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class OrderTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('week.first_day')
                    ->label('Week')
                    ->date()
                    ->sortable(),

                TextColumn::make('client.last_name')
                    ->label('Last name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.first_name')
                    ->label('First name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('meals_count')
                    ->label('Meals')
                    ->counts('meals')
                    ->sortable(),

                IconColumn::make('is_last_meal')
                    ->label('Last meal')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('last_meal')
                    ->label('Last meal orders')
                    ->query(fn (Builder $query) => $query->where('is_last_meal', true)),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function relation(Table $table): Table
    {
        return $table
            ->defaultSort('client.last_name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('client.last_name')
                    ->label('Last name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.first_name')
                    ->label('First name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('meals_count')
                    ->label('Meals')
                    ->counts('meals')
                    ->sortable(),

                IconColumn::make('is_last_meal')
                    ->label('Last meal')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('last_meal')
                    ->label('Last meal orders')
                    ->query(fn (Builder $query) => $query->where('is_last_meal', true)),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
