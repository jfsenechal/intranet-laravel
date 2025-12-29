<?php

namespace AcMarche\Hrm\Filament\Resources\Employees\Pages;

use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;
}
