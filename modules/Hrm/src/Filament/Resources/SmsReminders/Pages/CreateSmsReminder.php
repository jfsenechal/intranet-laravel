<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Pages;

use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use AcMarche\Hrm\Models\Employee;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class CreateSmsReminder extends CreateRecord
{
    #[Override]
    protected static string $resource = SmsReminderResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($employee = $this->getEmployeeFromQuery()) {
            return 'Ajouter un rappel sms pour '.$employee->last_name.' '.$employee->first_name;
        }

        return 'Ajouter un rappel sms ';
    }

    protected function fillForm(): void
    {
        $data = [];

        if ($employee = $this->getEmployeeFromQuery()) {
            $data['employee_id'] = $employee->id;
            $data['phone_number'] = $employee->private_mobile ?: $employee->private_phone;
        }

        $this->form->fill($data);
    }

    private function getEmployeeFromQuery(): ?Employee
    {
        $employeeId = request()->query('employee_id');

        return $employeeId ? Employee::find($employeeId) : null;
    }
}
