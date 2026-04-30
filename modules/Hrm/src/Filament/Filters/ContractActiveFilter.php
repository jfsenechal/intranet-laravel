<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

final class ContractActiveFilter
{
    public static function make(): TernaryFilter
    {
        return TernaryFilter::make('has_active_contract')
            ->label('Contrat actif')
            ->placeholder('Tous')
            ->trueLabel('Avec contrat actif')
            ->falseLabel('Sans contrat actif');
    }

    public static function makeWithContracts(): TernaryFilter
    {
        return self::make()
            ->queries(
                true: fn (Builder $query): Builder => $query->whereHas(
                    'employee.contracts',
                    fn (Builder $query) => $query->active(),
                ),
                false: fn (Builder $query): Builder => $query->whereDoesntHave(
                    'employee.contracts',
                    fn (Builder $query) => $query->active(),
                ),
            );
    }
}
