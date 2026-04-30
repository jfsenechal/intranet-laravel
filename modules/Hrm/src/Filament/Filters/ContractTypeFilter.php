<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use AcMarche\Hrm\Models\ContractType;
use Filament\Tables\Filters\SelectFilter;

final class ContractTypeFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('contract_type_id')
            ->label('Type')
            ->options(fn (): array => ContractType::groupedSelectOptions())
            ->searchable()
            ->preload();
    }
}
