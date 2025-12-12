<?php

namespace AcMarche\Courrier\Filament\Resources\ServiceResource\Pages;

use AcMarche\Courrier\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un service';
    }
}
