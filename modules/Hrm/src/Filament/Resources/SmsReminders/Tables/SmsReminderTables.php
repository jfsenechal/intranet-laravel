<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Tables;

use AcMarche\Hrm\Models\SmsReminder;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class SmsReminderTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('reminder_date', 'desc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn (SmsReminder $record): string => $record->employee?->last_name.' '.$record->employee?->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->label('Numéro')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('reminder_date')
                    ->label('Date de rappel')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('other_reminder_date')
                    ->label('Autre date de rappel')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sent_at')
                    ->label('Envoyé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('result')
                    ->label('Résultat')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_by')
                    ->label('Créé par')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('sent_at')
                    ->label('Envoi')
                    ->nullable()
                    ->placeholder('Tous')
                    ->trueLabel('Envoyés')
                    ->falseLabel('Non envoyés')
                    ->default(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class);
    }
}
