<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use AcMarche\Hrm\Models\Direction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class DirectionFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('direction_id')
            ->label('Direction')
            ->options(fn (): array => Direction::groupedSelectOptions())
            ->searchable()
            ->preload();
    }

    public static function makeWithContracts(): SelectFilter
    {
        return self::make()
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['value'] ?? null,
                fn (Builder $query, $serviceId): Builder => $query->whereHas(
                    'employee.contracts',
                    fn (Builder $query) => $query->where('direction_id', $serviceId),
                ),
            ));
    }
}
