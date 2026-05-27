<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contracts\Pages;

use AcMarche\Hrm\Filament\Exports\ContractExport;
use AcMarche\Hrm\Filament\Resources\Contracts\ContractResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListContracts extends ListRecords
{
    #[Override]
    protected static string $resource = ContractResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' contrats';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en XLSX')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->action(fn () => new ContractExport($this->getFilteredTableQuery())->downloadXlsx('contrats.xlsx')),
        ];
    }
}
