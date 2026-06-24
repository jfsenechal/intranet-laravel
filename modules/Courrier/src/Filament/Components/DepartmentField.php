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
        $departments = DepartmentScope::getAssignableDepartments();
        if (count($departments) === 0) {
            throw ValidationException::withMessages([
                'department' => "Vous n'êtes associé à aucun département.",
            ]);
        }
        if (count($departments) > 1) {
            throw ValidationException::withMessages([
                'department' => 'Vous êtes associés à plusieurs départements.',
            ]);
        }

        return
            TextInput::make('department')
                ->label('Département')
                ->formatStateUsing(fn (): string => $departments[0]->value)
                ->dehydrateStateUsing(fn (): string => $departments[0]->value)
                ->required()
                ->readOnly();

    }
}
