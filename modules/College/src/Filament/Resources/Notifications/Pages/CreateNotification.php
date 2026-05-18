<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications\Pages;

use AcMarche\College\Filament\Resources\Notifications\NotificationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateNotification extends CreateRecord
{
    #[Override]
    protected static string $resource = NotificationResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle notification';
    }
}
