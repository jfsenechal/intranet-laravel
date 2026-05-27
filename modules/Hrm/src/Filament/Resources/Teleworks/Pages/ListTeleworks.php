<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Pages;

use AcMarche\Hrm\Filament\Exports\TeleworkExport;
use AcMarche\Hrm\Filament\Resources\Teleworks\TeleworkResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListTeleworks extends ListRecords
{
    #[Override]
    protected static string $resource = TeleworkResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' demandes de télétravail';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en XLSX')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->action(fn () => new TeleworkExport($this->getFilteredTableQuery())->downloadXlsx('teletravail.xlsx')),
        ];
    }
}
