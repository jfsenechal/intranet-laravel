<?php

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Filament\Resources\News\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
