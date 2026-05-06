<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class WeekTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('first_day', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('first_day')
                    ->label('Commence le')
                    ->formatStateUsing(fn ($state): string => $state->translatedFormat('j F Y'))
                    ->sortable(),

                TextColumn::make('orders_count')
                    ->label('Commandes')
                    ->counts('orders')
                    ->sortable(),

                IconColumn::make('is_archived')
                    ->label('Archivée')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Active weeks')
                    ->query(fn (Builder $query) => $query->where('is_archived', false))
                    ->default(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
