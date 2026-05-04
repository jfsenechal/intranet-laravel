<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Participation\Tables;

use AcMarche\AldermenAgenda\Models\Participation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ParticipationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('event.title')
                    ->label('Événement')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('recipient.last_name')
                    ->label('Destinataire')
                    ->formatStateUsing(
                        fn ($state, Participation $record) => $record->recipient->last_name.' '.$record->recipient->first_name
                    )
                    ->searchable(),
                IconColumn::make('response')
                    ->label('Réponse')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('response')
                    ->label('Réponse')
                    ->boolean()
                    ->trueLabel('Répondu oui')
                    ->falseLabel('Répondu non')
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
