<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Directions\Pages;

use AcMarche\Hrm\Filament\Resources\Directions\DirectionResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateDirection extends CreateRecord
{
    #[Override]
    protected static string $resource = DirectionResource::class;
}
