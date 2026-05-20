<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\Pages;

use AcMarche\SportsActivities\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateActivity extends CreateRecord
{
    #[Override]
    protected static string $resource = ActivityResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle activité';
    }
}
