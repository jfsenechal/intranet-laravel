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
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department')
                    ->label('Département')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('functional_code')
                    ->label('Code fonctionnel')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('economic_code')
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
