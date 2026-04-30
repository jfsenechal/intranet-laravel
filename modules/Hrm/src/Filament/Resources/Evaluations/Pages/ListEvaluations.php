<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations\Pages;

use AcMarche\Hrm\Filament\Resources\Evaluations\EvaluationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListEvaluations extends ListRecords
{
    #[Override]
    protected static string $resource = EvaluationResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' évaluations';
    }
}
