<?php

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Filament\Resources\News\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }
}
