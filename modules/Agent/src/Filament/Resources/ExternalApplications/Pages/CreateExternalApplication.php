<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\ExternalApplications\Pages;

use AcMarche\Agent\Filament\Resources\ExternalApplications\ExternalApplicationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateExternalApplication extends CreateRecord
{
    #[Override]
    protected static string $resource = ExternalApplicationResource::class;
}
