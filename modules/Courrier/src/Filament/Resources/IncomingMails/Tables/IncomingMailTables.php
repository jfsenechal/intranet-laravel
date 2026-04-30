<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Tables;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Models\IncomingMail;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class IncomingMailTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('mail_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('reference_number')
                    ->searchable()
                    ->label('Référence')
                    ->url(fn (IncomingMail $record): string => IncomingMailResource::getUrl('view', ['record' => $record->id])),
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
                    ->expandableLimitedList(),
                TextColumn::make('recipients.full_name')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),
                IconColumn::make('is_notified')
                    ->label('Notifié')
                    ->boolean(),
                IconColumn::make('is_registered')
                    ->label('Recom')
                    ->boolean(),
                IconColumn::make('has_acknowledgment')
                    ->label('AR')
                    ->boolean(),
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
