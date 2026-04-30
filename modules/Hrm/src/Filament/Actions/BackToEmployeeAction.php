<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Actions;

use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

final class BackToEmployeeAction
{
    public static function make(): Action
    {
        return Action::make('viewEmployee')
            ->label('Retour à l\'employé')
            ->icon(Heroicon::ArrowUturnLeft)
            ->color('gray')
            ->visible(fn (Model $record): bool => ! empty($record->employee_id))
            ->url(fn (Model $record): string => EmployeeResource::getUrl('view', ['record' => $record->employee_id]));
    }
}
