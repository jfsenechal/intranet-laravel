<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groupes\Pages;

use AcMarche\Conseil\Filament\Resources\Groupes\GroupeResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateGroupe extends CreateRecord
{
    #[Override]
    protected static string $resource = GroupeResource::class;

    public function getTitle(): string
    {
        return 'Nouveau groupe';
    }
}
