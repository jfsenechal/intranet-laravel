<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class TicketTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('createdAt', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('number')
                    ->label('N°')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Motif')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(60),
                TextColumn::make('service')
                    ->label('Service')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('office.name')
                    ->label('Guichet')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('user_add')
                    ->label('Créé par')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('assigned_by')
                    ->label('Assigné par')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('createdAt')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                IconColumn::make('archive')
                    ->label('Archivé')
                    ->falseIcon(null)
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('archive')
                    ->label('Archivé')
                    ->boolean()
                    ->trueLabel('Archivés seulement')
                    ->falseLabel('Non archivés seulement')
                    ->default(false)
                    ->native(false),
                SelectFilter::make('office_id')
                    ->label('Guichet')
                    ->relationship('office', 'name'),
                SelectFilter::make('service')
                    ->label('Service')
                    ->options(fn (): array => \AcMarche\GuichetHdv\Models\Ticket::query()
                        ->distinct()
                        ->orderBy('service')
                        ->pluck('service', 'service')
                        ->all()),
                Filter::make('assigned')
                    ->label('Assignés')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('assigned_date')),
                Filter::make('unassigned')
                    ->label('En attente')
                    ->query(fn (Builder $query): Builder => $query->whereNull('assigned_date')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
