<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Services\Pages;

use AcMarche\Hrm\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateService extends CreateRecord
{
    #[Override]
    protected static string $resource = ServiceResource::class;
}
