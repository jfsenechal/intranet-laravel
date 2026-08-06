<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Jobs\Pages;

use AcMarche\Security\Filament\Resources\Jobs\JobResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListJobs extends ListRecords
{
    #[Override]
    protected static string $resource = JobResource::class;
}
