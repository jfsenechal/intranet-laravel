<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Sportifs\Pages;

use AcMarche\SportsActivities\Filament\Resources\Sportifs\SportifResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateSportif extends CreateRecord
{
    #[Override]
    protected static string $resource = SportifResource::class;

    public function getTitle(): string
    {
        return 'Nouveau sportif';
    }
}
