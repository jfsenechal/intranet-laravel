<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Pages;

use AcMarche\Hrm\Filament\Exports\AbsenceExport;
use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en XLSX')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->action(fn () => new AbsenceExport($this->getFilteredTableQuery())->downloadXlsx('absences.xlsx')),
        ];
    }
}
