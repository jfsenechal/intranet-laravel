<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Widgets;

use AcMarche\Hrm\Filament\Resources\Trainings\TrainingResource;
use AcMarche\Hrm\Models\Training;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Flex;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class TrainingRemindersWidget extends BaseWidget
{
    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Formations - rappels à venir')
            ->query(
                fn (): Builder => Training::query()
                    ->with('employee')
                    ->whereNotNull('reminder_date')
                    ->whereDate('reminder_date', '>=', today())
                    ->orderBy('reminder_date')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn (Training $record): string => mb_trim(($record->employee?->last_name ?? '').' '.($record->employee?->first_name ?? ''))
                    )
                    ->searchable(['last_name', 'first_name']),
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
                                ->label('Rappel Du'),
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
                    ->url(fn (Training $record): string => TrainingResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
