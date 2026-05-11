<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Widgets;

use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use AcMarche\Hrm\Models\SmsReminder;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class SmsReminderRemindersWidget extends BaseWidget
{
    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->heading('SMS - rappels à venir')
            ->query(
                fn (): Builder => SmsReminder::query()
                    ->with('employee')
                    ->whereNotNull('reminder_date')
                    ->whereDate('reminder_date', '>=', today())
                    ->orderBy('reminder_date')
            )
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn (SmsReminder $record): string => mb_trim(($record->employee?->last_name ?? '').' '.($record->employee?->first_name ?? ''))
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('reminder_date')
                    ->label('Rappel')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('other_reminder_date')
                    ->label('Autre rappel')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('sent_at')
                    ->label('Envoyé le')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultPaginationPageOption(5)
            ->recordActions([
                ViewAction::make()
                    ->url(fn (SmsReminder $record): string => SmsReminderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
