<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Pages;

use AcMarche\Offenses\Filament\Resources\Offenders\OffenderResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOffender extends CreateRecord
{
    #[Override]
    protected static string $resource = OffenderResource::class;
}
