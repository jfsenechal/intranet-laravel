<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('last_name')->label('Nom')->sortable()->searchable(),
                TextColumn::make('first_name')->label('Prénom')->sortable()->searchable(),
                TextColumn::make('city')->label('Localité')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('mobile')->label('GSM')->toggleable(),
                TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Inscriptions')
                    ->sortable(),
            ])
            ->defaultSort('last_name')
            ->filters([
                //
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
