<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Diplomas\Tables;

use AcMarche\Hrm\Filament\Filters\ContractActiveFilter;
use AcMarche\Hrm\Filament\Filters\DirectionFilter;
use AcMarche\Hrm\Filament\Filters\ServiceFilter;
use AcMarche\Hrm\Models\Diploma;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class DiplomaTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn(Diploma $record): string => $record->employee->last_name.' '.$record->employee->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('certificate_file')
                    ->label('Fichier')
                    ->formatStateUsing(fn(?string $state): string => $state ? '✓' : '—')
                    ->toggleable(),
                TextColumn::make('user_add')
                    ->label('Ajouté par')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->filters([
                DirectionFilter::makeWithContracts(),
                ServiceFilter::makeWithContracts(),
                ContractActiveFilter::makeWithContracts(),
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

    public static function relation(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('certificate_file')
                    ->label('Fichier')
                    ->formatStateUsing(fn(?string $state): string => $state ? '✓' : '—'),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordAction(ViewAction::class);
    }
}
