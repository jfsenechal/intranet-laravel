<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines\Pages;

use AcMarche\Hrm\Filament\Exports\DeadlineExport;
use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListDeadlines extends ListRecords
{
    #[Override]
    protected static string $resource = DeadlineResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' échéances';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une échéance')
                ->icon('tabler-plus'),
            Action::make('export')
                ->label('Exporter en XLSX')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->schema([
                    CheckboxList::make('columns')
                        ->label('Colonnes à exporter')
                        ->options(DeadlineExport::columns())
                        ->default(array_keys(DeadlineExport::columns()))
                        ->columns(2)
                        ->bulkToggleable()
                        ->required(),
                ])
                ->action(fn (array $data) => new DeadlineExport($this->getFilteredTableQuery(), $data['columns'])->downloadXlsx('echeances.xlsx')),
        ];
    }
}
