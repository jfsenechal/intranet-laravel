<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Widgets;

use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Services\AbsenceNotifier;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class LastAbsencesWidget extends BaseWidget
{
    private const int CESI_THRESHOLD_DAYS = 28;

    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Dernières absences nécessitant un suivi CESI')
            ->description('Absences en cours ou récentes avec encodage CESI requis (≥ 4 semaines) ou demande de potentiel de travail (≥ 8 semaines).')
            ->query(
                fn (): Builder => Absence::query()
                    ->with('employee')
                    ->whereRaw(
                        'DATEDIFF(COALESCE(end_date, CURDATE()), start_date) + 1 >= ?',
                        [self::CESI_THRESHOLD_DAYS]
                    )
                    ->orderByDesc('start_date')
            )
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn (Absence $record): string => $record->employee->last_name.' '.$record->employee->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->placeholder('En cours')
                    ->sortable(),
                TextColumn::make('cesi_alert')
                    ->label('CESI')
                    ->state(fn (Absence $record): ?string => app(AbsenceNotifier::class)->getCesiAlert($record))
                    ->color('info')
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('work_potential_alert')
                    ->label('Potentiel de travail')
                    ->state(fn (Absence $record): ?string => app(AbsenceNotifier::class)->getWorkPotentialAlert($record))
                    ->color('danger')
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Absence $record): string => AbsenceResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
