<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80),
                TextColumn::make('activity.name')
                    ->label('Activité')
                    ->sortable()
                    ->toggleable()
                    ->badge(),
                TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Inscrits')
                    ->sortable(),
                TextColumn::make('activity_schedules_count')
                    ->counts('activitySchedules')
                    ->label('Séances')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('activity_id')
                    ->label('Activité')
                    ->relationship('activity', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('en_cours')
                    ->label('En cours')
                    ->nullable()
                    ->queries(
                        true: fn (Builder $query): Builder => $query
                            ->whereDate('start_date', '<=', now())
                            ->where(fn (Builder $q): Builder => $q
                                ->whereNull('end_date')
                                ->orWhereDate('end_date', '>=', now())),
                        false: fn (Builder $query): Builder => $query
                            ->whereNotNull('end_date')
                            ->whereDate('end_date', '<', now()),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir')
                    ->icon(Heroicon::Eye),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
