<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Tables;

use AcMarche\Courrier\Repository\IncomingMailRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->html()
                    ->limit(80),
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
            ->filters([
                SelectFilter::make('services')
                    ->label('Service')
                    ->relationship('services', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('recipients')
                    ->label('Destinataire')
                    ->relationship('recipients', 'last_name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('is_notified')
                    ->label('Notifié'),
                TernaryFilter::make('is_registered')
                    ->label('Recommandé'),
                TernaryFilter::make('has_acknowledgment')
                    ->label('Accusé de réception'),
            ])
            ->persistFiltersInSession()
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
