<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective\Pages;

use AcMarche\Pst\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListOperationalObjectives extends ListRecords
{
    #[Override]
    protected static string $resource = OperationalObjectiveResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' Objectifs Opérationnels (OO)';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un objectif opérationnel')
                ->icon('tabler-plus'),
        ];
    }
}
