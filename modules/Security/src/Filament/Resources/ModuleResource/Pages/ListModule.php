<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\Pages;

use AcMarche\Security\Filament\Resources\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListModule extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' modules';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un module')
                ->icon('tabler-plus'),
        ];
    }

}
