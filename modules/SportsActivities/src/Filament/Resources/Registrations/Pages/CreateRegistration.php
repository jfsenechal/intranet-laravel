<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations\Pages;

use AcMarche\SportsActivities\Filament\Resources\Registrations\RegistrationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateRegistration extends CreateRecord
{
    #[Override]
    protected static string $resource = RegistrationResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle inscription';
    }
}
