<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Pages;

use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use AcMarche\Hrm\Models\Employee;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class CreateAbsence extends CreateRecord
{
    #[Override]
    protected static string $resource = AbsenceResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($employee = $this->getEmployeeFromQuery()) {
            return 'Ajouter une absence pour '.$employee->last_name.' '.$employee->first_name;
        }

        return 'Ajouter une absence';
    }

    protected function fillForm(): void
    {
        $data = [];

        if ($employee = $this->getEmployeeFromQuery()) {
            $data['employee_id'] = $employee->id;
        }

        $this->form->fill($data);
    }

    private function getEmployeeFromQuery(): ?Employee
    {
        $employeeId = request()->query('employee_id');

        return $employeeId ? Employee::find($employeeId) : null;
    }
}
