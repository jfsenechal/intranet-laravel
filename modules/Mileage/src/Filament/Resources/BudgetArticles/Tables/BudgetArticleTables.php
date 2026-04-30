<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\BudgetArticles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class BudgetArticleTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department')
                    ->label('Département')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('functional_code')
                    ->label('Code fonctionnel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('economic_code')
                    ->label('Code économique')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([

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
