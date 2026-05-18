<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Membres\Pages;

use AcMarche\ActivityManager\Filament\Resources\Membres\MembreResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateMembre extends CreateRecord
{
    #[Override]
    protected static string $resource = MembreResource::class;

    public function getTitle(): string
    {
        return 'Nouveau membre';
    }
}
