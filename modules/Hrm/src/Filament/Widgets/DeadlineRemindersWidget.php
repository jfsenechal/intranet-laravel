<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Widgets;

use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use AcMarche\Hrm\Models\Deadline;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Flex;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class DeadlineRemindersWidget extends BaseWidget
{
    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Échéances - rappels à venir')
            ->query(
                fn (): Builder => Deadline::query()
                    ->with(['employee', 'employer'])
                    ->where('is_closed', false)
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
                        fn (Deadline $record): string => mb_trim(($record->employee?->last_name ?? '').' '.($record->employee?->first_name ?? ''))
                    )
                    ->searchable(['last_name', 'first_name']),
                TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->label('Échéance')
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
                    ->url(fn (Deadline $record): string => DeadlineResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
