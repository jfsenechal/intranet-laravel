<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\FollowUp\Pages;

use AcMarche\Pst\Filament\Resources\FollowUp\FollowUpResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListFollowUps extends ListRecords
{
    #[Override]
    protected static string $resource = FollowUpResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' suivis';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un suivi')
                ->icon('tabler-plus'),
        ];
    }
}
