<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Absences\Pages;

use AcMarche\MealDelivery\Filament\Resources\Absences\AbsenceResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListAbsences extends ListRecords
{
    #[Override]
    protected static string $resource = AbsenceResource::class;

    public function getTitle(): string
    {
        return 'Liste des absences';
    }
}
