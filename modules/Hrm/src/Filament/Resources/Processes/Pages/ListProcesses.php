<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Pages;

use AcMarche\Hrm\Filament\Exports\ProcessExport;
use AcMarche\Hrm\Filament\Resources\Processes\ProcessResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListProcesses extends ListRecords
{
    #[Override]
    protected static string $resource = ProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau processus')
                ->icon(Heroicon::Plus),
            Action::make('export')
                ->label('Exporter en XLSX')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->schema([
                    CheckboxList::make('columns')
                        ->label('Colonnes à exporter')
                        ->options(ProcessExport::columns())
                        ->default(array_keys(ProcessExport::columns()))
                        ->columns(2)
                        ->bulkToggleable()
                        ->required(),
                ])
                ->action(fn (array $data) => new ProcessExport($this->getFilteredTableQuery(), $data['columns'])->downloadXlsx('processus.xlsx')),
        ];
    }
}
