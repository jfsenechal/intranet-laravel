<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Tables;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class IncomingMailTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => IncomingMailRepository::scopeToTodayForCurrentUser($query)
            )
            ->defaultSort('mail_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('reference_number')
                    ->searchable()
                    ->label('Référence'),
                TextColumn::make('mail_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Date'),
                TextColumn::make('sender')
                    ->searchable()
                    ->label('Expéditeur'),
                TextColumn::make('description')
                    ->searchable()
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                TextColumn::make('recipients.full_name')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                IconColumn::make('is_notified')
                    ->label('Notifié')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_registered')
                    ->label('Recommandé')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_acknowledgment')
                    ->label('Accusé de réception')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('department')
                    ->label('Département')
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function forAdvanceSearch(Table $table, Builder $builder): Table
    {
        return $table
            ->query(fn (): Builder => $builder)
            ->emptyStateHeading('Aucun courrier trouvé')
            ->defaultPaginationPageOption(50)
            ->paginated([25, 50, 100])
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Référence'),
                TextColumn::make('id')
                    ->label('Numéro')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mail_date')
                    ->date('d/m/Y')
                    ->label('Date'),
                TextColumn::make('sender')
                    ->label('Expéditeur'),
                TextColumn::make('description')
                    ->label('Description')
                    ->html()
                    ->limit(80)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                TextColumn::make('recipients.full_name')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                IconColumn::make('is_registered')
                    ->label('Recommandé')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn (IncomingMail $record): string => IncomingMailResource::getUrl('view', ['record' => $record])
            );
    }
}
