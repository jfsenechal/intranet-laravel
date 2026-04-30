<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Filament\Resources\News\NewsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListNews extends ListRecords
{
    #[Override]
    protected static string $resource = NewsResource::class;

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
