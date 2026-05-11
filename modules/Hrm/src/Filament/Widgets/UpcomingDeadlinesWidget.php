<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Widgets;

use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use AcMarche\Hrm\Models\Deadline;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class UpcomingDeadlinesWidget extends BaseWidget
{
    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Prochaines échéances')
            ->description('Échéances dont la date de fin est à venir.')
            ->query(
                fn (): Builder => Deadline::query()
                    ->with(['employee', 'employer'])
                    ->where('is_closed', false)
                    ->whereDate('end_date', '>=', today())
                    ->orderBy('end_date')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable(),
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
                    ->sortable(),
                TextColumn::make('reminder_date')
                    ->label('Rappel')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Deadline $record): string => DeadlineResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
