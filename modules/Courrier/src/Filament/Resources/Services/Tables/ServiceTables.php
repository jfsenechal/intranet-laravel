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
                    DeleteBulkAction::make()
                        ->accessSelectedRecords()
                        ->modalDescription(self::attachedMailsWarning(...)),
                ]),
            ]);
    }

    /**
     * Warns that every courrier routed to the selected services will be unlinked.
     * Returning null leaves Filament's default confirmation text in place.
     */
    private static function attachedMailsWarning(DeleteBulkAction $action): ?string
    {
        $count = (int) $action->getSelectedRecordsQuery()
            ->withCount('incomingMails')
            ->get()
            ->sum('incoming_mails_count');

        if ($count === 0) {
            return null;
        }

        return $count === 1
            ? 'La sélection est liée à 1 courrier. Il sera détaché, mais ne sera pas supprimé. Voulez-vous continuer ?'
            : "La sélection est liée à {$count} courriers. Ils seront détachés, mais ne seront pas supprimés. Voulez-vous continuer ?";
    }
}
