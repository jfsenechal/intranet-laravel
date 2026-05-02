<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Participation\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Participation\ParticipationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateParticipation extends CreateRecord
{
    #[Override]
    protected static string $resource = ParticipationResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une participation';
    }
}
