<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Services\Pages;

use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateService extends CreateRecord
{
    #[Override]
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
