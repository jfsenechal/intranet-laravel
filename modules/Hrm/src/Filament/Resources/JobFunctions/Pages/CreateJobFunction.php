<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\JobFunctions\Pages;

use AcMarche\Hrm\Filament\Resources\JobFunctions\JobFunctionResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateJobFunction extends CreateRecord
{
    #[Override]
    protected static string $resource = JobFunctionResource::class;
}
