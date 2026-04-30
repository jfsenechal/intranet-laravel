<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class EvaluationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('evaluation_date', 'desc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('evaluation_date')
                    ->label('Date évaluation')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('next_evaluation_date')
                    ->label('Prochaine évaluation')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('validation_date')
                    ->label('Validation')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('result')
                    ->label('Résultat')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('direction.name')
                    ->label('Direction')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class);
    }
}
