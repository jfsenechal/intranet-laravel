<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Diplomas\Pages;

use AcMarche\Hrm\Filament\Resources\Diplomas\DiplomaResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListDiplomas extends ListRecords
{
    #[Override]
    protected static string $resource = DiplomaResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' diplômes';
    }
}
