<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class MealTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('order.client.last_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('soup_count')
                    ->label('Soups')
                    ->sortable(),

                TextColumn::make('menus_count')
                    ->label('Menus')
                    ->counts('menus')
                    ->sortable(),

                IconColumn::make('at_cafeteria')
                    ->label('Cafeteria')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
