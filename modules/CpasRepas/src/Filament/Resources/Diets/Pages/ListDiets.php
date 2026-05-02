<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Diets\Pages;

use AcMarche\CpasRepas\Filament\Resources\Diets\DietResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListDiets extends ListRecords
{
    #[Override]
    protected static string $resource = DietResource::class;

    public function getTitle(): string
    {
        return 'Diets';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add diet')
                ->icon('tabler-plus'),
        ];
    }
}
