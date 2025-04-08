<?php

namespace AcMarche\Security\Filament\Resources\UserResource\Pages;

use AcMarche\Security\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Contracts\Support\Htmlable;
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' agents';
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
