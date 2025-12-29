<?php

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Filament\Resources\News\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' actualités';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une actualité')
                ->icon('tabler-plus'),
        ];
    }
}
