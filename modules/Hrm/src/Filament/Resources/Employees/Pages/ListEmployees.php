<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\Pages;

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Exports\EmployeeExport;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Override;

final class ListEmployees extends ListRecords
{
    #[Override]
    protected static string $resource = EmployeeResource::class;

    public function getTitle(): string|Htmlable
    {
        $statusValue = $this->tableFilters['status']['value'] ?? null;
        $label = $statusValue ? StatusEnum::from($statusValue)->getLabel().'s' : 'agents';

        return $this->getAllTableRecordsCount().' '.$label;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un agent')
                ->icon('tabler-plus'),
            Action::make('export')
                ->label('Exporter en CSV')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->action(fn () => ExcelFacade::download(
                    new EmployeeExport($this->getFilteredTableQuery()),
                    'agents.csv',
                    Excel::CSV,
                )),
        ];
    }
}
