<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('member.first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('activity.name')
                    ->label('Activité')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('group.day')
                    ->label('Jour'),
                TextColumn::make('group.location')
                    ->label('Lieu'),
                TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR'),
                TextColumn::make('created_at')
                    ->label('Inscription')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('activity_id')
                    ->label('Activité')
                    ->relationship('activity', 'name'),
                SelectFilter::make('group_id')
                    ->label('Groupe')
                    ->relationship('group', 'day'),
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
