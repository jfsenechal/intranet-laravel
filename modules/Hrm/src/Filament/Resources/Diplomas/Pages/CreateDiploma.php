<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Diplomas\Pages;

use AcMarche\Hrm\Filament\Resources\Diplomas\DiplomaResource;
use AcMarche\Hrm\Models\Employee;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class CreateDiploma extends CreateRecord
{
    #[Override]
    protected static string $resource = DiplomaResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($employee = $this->getEmployeeFromQuery()) {
            return 'Ajouter un diplôme pour '.$employee->last_name.' '.$employee->first_name;
        }

        return 'Ajouter un diplôme';
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
