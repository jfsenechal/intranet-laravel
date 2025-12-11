<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMailResource\Tables;

use AcMarche\Courrier\Filament\Resources\IncomingMailResource;
use AcMarche\Courrier\Models\IncomingMail;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class IncomingMailTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('received_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->label('Référence')
                    ->url(fn (IncomingMail $record) => IncomingMailResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('received_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Date de réception'),
                Tables\Columns\TextColumn::make('sender_name')
                    ->searchable()
                    ->label('Expéditeur'),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->label('Objet')
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'processed' => 'Traité',
                        'archived' => 'Archivé',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processed' => 'success',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assigned_to')
                    ->searchable()
                    ->label('Assigné à'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'processed' => 'Traité',
                        'archived' => 'Archivé',
                    ]),
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
