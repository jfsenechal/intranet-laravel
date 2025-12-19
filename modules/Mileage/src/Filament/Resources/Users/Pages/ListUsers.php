<?php

namespace AcMarche\Mileage\Filament\Resources\Users\Pages;

use AcMarche\Mileage\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Liste des agents';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Liste des agents ayant accès au module "Frais de déplacement"';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un agent')
                ->icon('tabler-plus'),
        ];
    }
}
