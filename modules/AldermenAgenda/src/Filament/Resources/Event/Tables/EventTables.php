<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Event\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class EventTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('title')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('event_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('organizer')
                    ->label('Organisateur')
                    ->searchable(),
                IconColumn::make('sent')
                    ->label('Envoyé')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('sent')
                    ->label('Envoyé')
                    ->boolean()
                    ->trueLabel('Envoyés seulement')
                    ->falseLabel('Non envoyés seulement')
                    ->native(false),
                TernaryFilter::make('is_local')
                    ->label('Sur la commune')
                    ->boolean()
                    ->trueLabel('Sur la commune seulement')
                    ->falseLabel('Pas sur la commune seulement')
                    ->native(false),
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
}
