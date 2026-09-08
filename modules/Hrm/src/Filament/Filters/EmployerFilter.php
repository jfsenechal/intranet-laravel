<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use AcMarche\Hrm\Models\Employer;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class EmployerFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('employer_id')
            ->label('Employeur')
            ->options(fn (): array => Employer::groupedSelectOptions())
            ->searchable()
            ->preload()
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['value'] ?? null,
                fn (Builder $query, $employerId): Builder => $query->whereIn(
                    'employer_id',
                    Employer::descendantsAndSelfIds((int) $employerId),
                ),
            ));

    }

    /**
     * @param  bool  $activeOnly  Match only through contracts that are still
     *                            running. Leave it off for listings of past
     *                            records (absences, trainings), where the
     *                            employer of the time is what matters.
     */
    public static function makeThrough(string $relation, bool $activeOnly = false): SelectFilter
    {
        return SelectFilter::make('employer_id')
            ->label('Employeur')
            ->options(fn (): array => Employer::groupedSelectOptions())
            ->searchable()
            ->preload()
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['value'] ?? null,
                fn (Builder $query, $employerId): Builder => $query->whereHas(
                    $relation,
                    function (Builder $query) use ($employerId, $activeOnly): void {
                        $query->whereIn(
                            'employer_id',
                            Employer::descendantsAndSelfIds((int) $employerId),
                        );

                        if ($activeOnly) {
                            $query->active();
                        }
                    },
                ),
            ));
    }
}
