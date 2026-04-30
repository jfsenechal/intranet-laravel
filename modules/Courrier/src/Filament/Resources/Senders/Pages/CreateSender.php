<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Senders\Pages;

use AcMarche\Courrier\Filament\Resources\Senders\SenderResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateSender extends CreateRecord
{
    #[Override]
    protected static string $resource = SenderResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un expéditeur';
    }
}
