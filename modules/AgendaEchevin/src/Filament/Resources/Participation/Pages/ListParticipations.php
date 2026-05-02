<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Participation\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Participation\ParticipationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListParticipations extends ListRecords
{
    #[Override]
    protected static string $resource = ParticipationResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' participations';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une participation')
                ->icon('tabler-plus'),
        ];
    }
}
