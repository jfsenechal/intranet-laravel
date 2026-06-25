<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Components;

use AcMarche\Courrier\Repository\DepartmentScope;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\ValidationException;

final class DepartmentField
{
    public static function make(): TextInput
    {
        $department = DepartmentScope::getAssignableDepartment();
        if (! $department) {
            throw ValidationException::withMessages([
                'department' => "Vous n'êtes associé à aucun département. (ROLE_INDICATEUR_ADMIN)",
            ]);
        }

        return
            TextInput::make('department')
                ->label('Département')
                ->formatStateUsing(fn (): string => $department->value)
                ->dehydrateStateUsing(fn (): string => $department->value)
                ->required()
                ->readOnly();

    }
}
