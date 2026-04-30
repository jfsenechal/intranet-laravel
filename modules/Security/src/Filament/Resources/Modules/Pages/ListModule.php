<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Pages;

use AcMarche\Security\Filament\Resources\Modules\ModuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListModule extends ListRecords
{
    #[Override]
    protected static string $resource = ModuleResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' modules';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un module')
                ->icon('tabler-plus'),
        ];
    }
}
