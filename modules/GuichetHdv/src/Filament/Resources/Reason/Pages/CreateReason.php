<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Reason\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Reason\ReasonResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateReason extends CreateRecord
{
    #[Override]
    protected static string $resource = ReasonResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un motif';
    }
}
