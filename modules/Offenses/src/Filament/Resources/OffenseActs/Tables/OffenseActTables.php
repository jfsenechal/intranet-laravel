<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OffenseActTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('offenses_count')
                    ->label('Sanctions')
                    ->counts('offenses')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    // A used act refuses to be deleted, so the bulk action reports it as a failed
                    // record and the untouched acts stay in the table.
                    DeleteBulkAction::make()
                        ->modalDescription("Seuls les types d'actes qui ne sont utilisés par aucune incivilité seront supprimés."),
                ]),
            ]);
    }
}
