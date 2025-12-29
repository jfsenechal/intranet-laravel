<?php

namespace AcMarche\Publication\Filament\Resources\Publications\Pages;

use AcMarche\Publication\Filament\Resources\Publications\PublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

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
