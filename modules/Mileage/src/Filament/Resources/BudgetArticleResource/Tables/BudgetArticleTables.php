<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticleResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetArticleTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nom')
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('departement')
                    ->label('Département')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fonctionnel')
                    ->label('Code fonctionnel')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('economique')
                    ->label('Code économique')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
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
