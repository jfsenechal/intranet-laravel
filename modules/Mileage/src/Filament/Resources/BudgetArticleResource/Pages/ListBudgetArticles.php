<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticleResource\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetArticles extends ListRecords
{
    protected static string $resource = BudgetArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un article budgétaire')
                ->icon('tabler-plus'),
        ];
    }
}
