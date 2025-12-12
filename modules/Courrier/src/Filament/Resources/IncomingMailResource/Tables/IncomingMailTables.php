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
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->label('Référence')
                    ->url(fn (IncomingMail $record) => IncomingMailResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('mail_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Date'),
                Tables\Columns\TextColumn::make('sender')
                    ->searchable()
                    ->label('Expéditeur'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->label('Description')
                    ->html()
                    ->limit(80),
                Tables\Columns\TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),
                Tables\Columns\TextColumn::make('recipients.full_name')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),
                Tables\Columns\IconColumn::make('is_notified')
                    ->label('Notifié')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_registered')
                    ->label('Reco')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_acknowledgment')
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
