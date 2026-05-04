<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class NoteTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('note_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('note_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('client.last_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(80)
                    ->searchable(),

                IconColumn::make('is_done')
                    ->label('Done')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('done_by')
                    ->label('Done by')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('pending')
                    ->label('Pending notes')
                    ->query(fn (Builder $query) => $query->where('is_done', false))
                    ->default(),
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
}
