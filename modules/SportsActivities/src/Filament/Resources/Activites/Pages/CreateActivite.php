<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activites\Pages;

use AcMarche\SportsActivities\Filament\Resources\Activites\ActiviteResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateActivite extends CreateRecord
{
    #[Override]
    protected static string $resource = ActiviteResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle activité';
    }
}
