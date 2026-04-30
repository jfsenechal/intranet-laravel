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

    public static function makeThrough(string $relation): SelectFilter
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
                    fn (Builder $query) => $query->whereIn(
                        'employer_id',
                        Employer::descendantsAndSelfIds((int) $employerId),
                    ),
                ),
            ));
    }
}
