<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages;

use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListClassifiedAd extends ListRecords
{
    #[Override]
    protected static string $resource = ClassifiedAdResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' actualités';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une actualité')
                ->icon('tabler-plus'),
        ];
    }
}
