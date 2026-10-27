<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListCitoyens extends ListRecords
{
    protected static string $resource = CitoyenResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' citoyens';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
