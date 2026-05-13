<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Absences\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class AbsenceTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('end_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([

                TextColumn::make('client.last_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.first_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Du')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Au')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
