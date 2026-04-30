<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Components;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Repository\DepartmentScope;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;

final class DepartmentField
{
    /**
     * @return array<Select|Hidden>
     */
    public static function make(): array
    {
        $departments = DepartmentScope::getCurrentUserDepartments();

        if (count($departments) > 1) {
            return [
                Select::make('department')
                    ->label('Département')
                    ->options(
                        collect($departments)
                            ->mapWithKeys(fn (DepartmentCourrierEnum $d): array => [$d->value => $d->value])
                            ->all()
                    )
                    ->required(),
            ];
        }

        if (count($departments) === 1) {
            return [
                Hidden::make('department')
                    ->default($departments[0]->value),
            ];
        }

        return [];
    }
}
