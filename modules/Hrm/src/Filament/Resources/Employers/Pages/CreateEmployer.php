<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\Pages;

use AcMarche\Hrm\Filament\Resources\Employers\EmployerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEmployer extends CreateRecord
{
    protected static string $resource = EmployerResource::class;
}
