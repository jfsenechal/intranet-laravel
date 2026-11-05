<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class ListCitoyens extends ListRecords
{
    protected static string $resource = CitoyenResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' citoyens';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un citoyen')
                ->icon(Heroicon::Plus),
        ];
    }
}
