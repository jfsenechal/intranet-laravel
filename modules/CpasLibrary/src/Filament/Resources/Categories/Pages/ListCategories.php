<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Categories\CategorieResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListCategories extends ListRecords
{
    #[Override]
    protected static string $resource = CategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle catégorie')
                ->icon(Heroicon::Plus),
        ];
    }
}
