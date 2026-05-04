<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Pages;

use AcMarche\Offenses\Filament\Resources\OffenseActs\OffenseActResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOffenseAct extends CreateRecord
{
    #[Override]
    protected static string $resource = OffenseActResource::class;
}
