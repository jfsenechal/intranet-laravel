<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ServiceTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('initials')
                    ->label('Initiales')
                    ->searchable(),
                TextColumn::make('recipients_count')
                    ->label('Destinataires')
                    ->counts('recipients')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('incoming_mails_count')
                    ->label('Courriers')
                    ->counts('incomingMails')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('department')
                    ->label('Département')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('without_recipients')
                    ->label('Destinataires')
                    ->placeholder('Tous')
                    ->trueLabel('Sans destinataire')
                    ->falseLabel('Avec destinataires')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereDoesntHave('recipients'),
                        false: fn (Builder $query): Builder => $query->whereHas('recipients'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                TernaryFilter::make('without_incoming_mails')
                    ->label('Courriers')
                    ->placeholder('Tous')
                    ->trueLabel('Sans courrier')
                    ->falseLabel('Avec courriers')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereDoesntHave('incomingMails'),
                        false: fn (Builder $query): Builder => $query->whereHas('incomingMails'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
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
