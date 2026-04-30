<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use AcMarche\Hrm\Models\Service;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class ServiceFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('service_id')
            ->label('Service')
            ->options(fn (): array => Service::groupedSelectOptions())
            ->searchable()
            ->preload();
    }

    public static function makeWithContracts(): SelectFilter
    {
        return self::make()
            ->query(
                fn (Builder $query, array $data): Builder => $query->when(
                    $data['value'] ?? null,
                    fn (Builder $query, $directionId): Builder => $query->whereHas(
                        'employee.contracts',
                        fn (Builder $query) => $query->where('service_id', $directionId),
                    ),
                )
            );
    }
}
