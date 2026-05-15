<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Inscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class InscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sportif.nom')->label('Nom')->sortable()->searchable(),
                TextColumn::make('sportif.prenom')->label('Prénom')->sortable()->searchable(),
                TextColumn::make('activite.nom')->label('Activité')->sortable()->searchable(),
                TextColumn::make('groupe.jour')->label('Jour'),
                TextColumn::make('groupe.lieux')->label('Lieu'),
                TextColumn::make('prix')->label('Prix')->money('EUR')->sortable(),
                TextColumn::make('created_at')->label('Inscription')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('activite_id')
                    ->label('Activité')
                    ->relationship('activite', 'nom'),
                SelectFilter::make('groupe_id')
                    ->label('Groupe')
                    ->relationship('groupe', 'jour'),
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
                        ->label('Supprimer la selection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
