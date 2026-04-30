<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines\Pages;

use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use AcMarche\Hrm\Models\Employee;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

final class CreateDeadline extends CreateRecord
{
    protected static string $resource = DeadlineResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($employee = $this->getEmployeeFromQuery()) {
            return 'Ajouter une échéance pour '.$employee->last_name.' '.$employee->first_name;
        }

        return 'Ajouter une échéance';
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
