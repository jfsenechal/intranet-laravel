<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use AcMarche\Hrm\Models\ContractNature;
use Filament\Tables\Filters\SelectFilter;

final class ContractNatureFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('contract_nature_id')
            ->label('Nature')
            ->options(fn (): array => ContractNature::groupedSelectOptions())
            ->searchable()
            ->preload();
    }
}
