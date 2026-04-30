<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Rates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RateTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('start_date')
                    ->label('Date de début')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Date de fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable()
                    ->suffix(' €/km'),
                TextColumn::make('omnium')
                    ->label('Omnium')
                    ->money('EUR')
                    ->sortable()
                    ->suffix(' €/km'),
            ])
            ->filters([
                //
            ])
            ->recordActions([

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
