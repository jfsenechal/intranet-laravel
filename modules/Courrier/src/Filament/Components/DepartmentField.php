<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Components;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Repository\DepartmentScope;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Validation\ValidationException;

final class DepartmentField
{
    public static function make(): Select|Hidden
    {
        $departments = DepartmentScope::getCurrentUserDepartments();
        if (count($departments) === 0) {
            throw ValidationException::withMessages([
                'department' => "Vous n'êtes associé à aucun département.",
            ]);
        }

        if (count($departments) === 1) {
            return
                Hidden::make('department')
                    ->default($departments[0]->value);
        }

        return
            Select::make('department')
                ->label('Département')
                ->options(
                    collect($departments)
                        ->mapWithKeys(fn (DepartmentCourrierEnum $d): array => [$d->value => $d->value])
                        ->all()
                )
                ->default($departments[0]->value)
                ->required();

    }
}
