<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Prerequisites\Pages;

use AcMarche\Hrm\Filament\Resources\Prerequisites\PrerequisiteResource;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Prerequisite;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class CreatePrerequisite extends CreateRecord
{
    #[Override]
    protected static string $resource = PrerequisiteResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($employee = $this->getEmployeeFromQuery()) {
            return 'Ajouter un prérequis pour '.$employee->last_name.' '.$employee->first_name;
        }

        return 'Ajouter un prérequis';
    }

    protected function afterCreate(): void
    {
        if ($employee = $this->getEmployeeFromQuery()) {
            /** @var Prerequisite $prerequisite */
            $prerequisite = $this->record;
            $employee->prerequisite_id = $prerequisite->id;
            $employee->save();
        }
    }

    private function getEmployeeFromQuery(): ?Employee
    {
        $employeeId = request()->query('employee_id');

        return $employeeId ? Employee::find($employeeId) : null;
    }
}
