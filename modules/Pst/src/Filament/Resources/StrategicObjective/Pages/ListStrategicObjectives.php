<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective\Pages;

use AcMarche\Pst\Filament\Exports\StrategicObjectiveExport;
use AcMarche\Pst\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use AcMarche\Security\Repository\UserRepository;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Override;

final class ListStrategicObjectives extends ListRecords
{
    #[Override]
    protected static string $resource = StrategicObjectiveResource::class;

    #[Override]
    protected string $view = 'pst::filament.resources.strategic-objective-list';

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' objectifs stratégiques (OS)';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en Xlsx')
                ->icon('tabler-download')
                ->color('secondary')
                ->action(
                    fn () => Excel::download(
                        new StrategicObjectiveExport(UserRepository::departmentSelected()),
                        'pst.xlsx'
                    )
                ),
            CreateAction::make()
                ->label('Ajouter un OS')
                ->icon('tabler-plus'),
        ];
    }
}
