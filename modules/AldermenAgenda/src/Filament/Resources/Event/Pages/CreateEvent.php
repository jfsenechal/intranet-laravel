<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Event\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Event\EventResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateEvent extends CreateRecord
{
    #[Override]
    protected static string $resource = EventResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un événement';
    }
}
