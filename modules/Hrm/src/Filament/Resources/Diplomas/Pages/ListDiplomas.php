<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Diplomas\Pages;

use AcMarche\Hrm\Filament\Exports\DiplomaExport;
use AcMarche\Hrm\Filament\Resources\Diplomas\DiplomaResource;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
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
                        ->options(DiplomaExport::columns())
                        ->default(array_keys(DiplomaExport::columns()))
                        ->columns(2)
                        ->bulkToggleable()
                        ->required(),
                ])
                ->action(fn (array $data) => new DiplomaExport($this->getFilteredTableQuery(), $data['columns'])->downloadXlsx('diplomes.xlsx')),
        ];
    }
}
