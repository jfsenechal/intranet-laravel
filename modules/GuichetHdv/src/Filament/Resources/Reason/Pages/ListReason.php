<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Reason\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Reason\ReasonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListReason extends ListRecords
{
    #[Override]
    protected static string $resource = ReasonResource::class;

    public function getTitle(): string
    {
        return 'Motifs ('.$this->getAllTableRecordsCount().')';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un motif')
                ->icon('tabler-plus'),
        ];
    }
}
