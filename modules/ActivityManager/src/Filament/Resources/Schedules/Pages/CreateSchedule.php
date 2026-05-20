<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Pages;

use AcMarche\ActivityManager\Filament\Resources\Schedules\SchedulesResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateSchedule extends CreateRecord
{
    #[Override]
    protected static string $resource = SchedulesResource::class;

    public function getTitle(): string
    {
        return 'Nouveau cours';
    }
}
