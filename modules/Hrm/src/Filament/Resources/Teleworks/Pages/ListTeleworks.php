<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Pages;

use AcMarche\Hrm\Filament\Resources\Teleworks\TeleworkResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListTeleworks extends ListRecords
{
    #[Override]
    protected static string $resource = TeleworkResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' télétravail';
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
