<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticle\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticle\BudgetArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListBudgetArticles extends ListRecords
{
    protected static string $resource = BudgetArticleResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Liste des articles budgétaires';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un article budgétaire')
                ->icon('tabler-plus'),
        ];
    }
}
