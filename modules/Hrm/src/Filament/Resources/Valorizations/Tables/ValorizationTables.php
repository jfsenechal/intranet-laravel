<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Valorizations\Tables;

use AcMarche\Hrm\Models\Valorization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ValorizationTables
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
                        fn (Valorization $record): string => $record->employee->last_name.' '.$record->employee->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('employer_name')
                    ->label('Employeur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Durée')
                    ->sortable(),
                TextColumn::make('regime')
                    ->label('Régime')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('content')
                    ->label('Contenu')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('file_name')
                    ->label('Fichier')
                    ->formatStateUsing(fn (?string $state): string => $state ? '✓' : '—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('employer_name')
                    ->label('Employeur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Durée')
                    ->sortable(),
                TextColumn::make('regime')
                    ->label('Régime')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('content')
                    ->label('Contenu')
                    ->limit(60)
                    ->toggleable(),
            ]);
    }
}
