<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CaseFileTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('introduction_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('number')
                    ->label('N°')
                    ->sortable(),

                TextColumn::make('nature')
                    ->label('Nature')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('complainant.last_name')
                    ->label('Plaignant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agreementType.name')
                    ->label("Type d'accord")
                    ->searchable()
                    ->sortable(),

                TextColumn::make('introduction_date')
                    ->label("Date d'introduction")
                    ->date()
                    ->sortable(),

                TextColumn::make('closing_date')
                    ->label('Date de clôture')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
