<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groups\Pages;

use AcMarche\SportsActivities\Filament\Resources\Groups\GroupResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateGroup extends CreateRecord
{
    #[Override]
    protected static string $resource = GroupResource::class;

    public function getTitle(): string
    {
        return 'Nouveau groupe';
    }
}
