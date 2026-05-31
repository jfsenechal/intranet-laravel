<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Trainings\Pages;

use AcMarche\Hrm\Filament\Exports\TrainingExport;
use AcMarche\Hrm\Filament\Resources\Trainings\TrainingResource;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListTrainings extends ListRecords
{
    #[Override]
    protected static string $resource = TrainingResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' formations';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en XLSX')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->schema([
                    CheckboxList::make('columns')
                        ->label('Colonnes à exporter')
                        ->options(TrainingExport::columns())
                        ->default(array_keys(TrainingExport::columns()))
                        ->columns(2)
                        ->bulkToggleable()
                        ->required(),
                ])
                ->action(fn (array $data) => new TrainingExport($this->getFilteredTableQuery(), $data['columns'])->downloadXlsx('formations.xlsx')),
        ];
    }
}
