<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Pages;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOffense extends CreateRecord
{
    #[Override]
    protected static string $resource = OffenseResource::class;
}
