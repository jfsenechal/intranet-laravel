<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Cours\Tables;

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

final class CoursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date_debut', 'desc')
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80),
                TextColumn::make('activite.nom')
                    ->label('Activité')
                    ->sortable()
                    ->toggleable()
                    ->badge(),
                TextColumn::make('date_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('membres_count')
                    ->counts('membres')
                    ->label('Inscrits')
                    ->sortable(),
                TextColumn::make('dates_cours_count')
                    ->counts('datesCours')
                    ->label('Séances')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('activite_id')
                    ->label('Activité')
                    ->relationship('activite', 'nom')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('en_cours')
                    ->label('En cours')
                    ->nullable()
                    ->queries(
                        true: fn (Builder $query): Builder => $query
                            ->whereDate('date_debut', '<=', now())
                            ->where(fn (Builder $q): Builder => $q
                                ->whereNull('date_fin')
                                ->orWhereDate('date_fin', '>=', now())),
                        false: fn (Builder $query): Builder => $query
                            ->whereNotNull('date_fin')
                            ->whereDate('date_fin', '<', now()),
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
