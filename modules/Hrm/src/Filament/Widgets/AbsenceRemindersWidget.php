<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Widgets;

use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use AcMarche\Hrm\Models\Absence;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Flex;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class AbsenceRemindersWidget extends BaseWidget
{
    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Absences - rappels à venir')
            ->query(
                fn (): Builder => Absence::query()
                    ->with('employee')
                    ->whereNotNull('reminder_date')
                    ->whereDate('reminder_date', '>=', today())
                    ->orderBy('reminder_date')
            )
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn (Absence $record): string => mb_trim(($record->employee?->last_name ?? '').' '.($record->employee?->first_name ?? ''))
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Raison')
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('reminder_date')
                    ->label('Rappel')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->filters([
                Filter::make('reminder_date')
                    ->label('Date de rappel')
                    ->schema([
                        Flex::make([
                            DatePicker::make('from')
                                ->label('Du'),
                            DatePicker::make('until')
                                ->label('Au'),
                        ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('reminder_date', '>=', $date),
                        )
                        ->when(
                            $data['until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('reminder_date', '<=', $date),
                        )),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Absence $record): string => AbsenceResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
