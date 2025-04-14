<?php

namespace AcMarche\News\Filament\Resources\NewsResource\Pages;

use AcMarche\News\Filament\Resources\NewsResource;
use Filament\Actions;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Resources\Pages\EditRecord;

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
