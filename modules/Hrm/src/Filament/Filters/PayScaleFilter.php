<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use AcMarche\Hrm\Models\PayScale;
use Filament\Tables\Filters\SelectFilter;

final class PayScaleFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('pay_scale_id')
            ->label('Echelle')
            ->options(fn (): array => PayScale::groupedSelectOptions())
            ->searchable()
            ->preload();
    }
}
