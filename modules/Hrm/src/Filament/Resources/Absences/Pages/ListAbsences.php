<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Pages;

use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListAbsences extends ListRecords
{
    #[Override]
    protected static string $resource = AbsenceResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' absences';
    }
}
