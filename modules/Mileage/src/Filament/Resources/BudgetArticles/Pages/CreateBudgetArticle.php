<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticles\BudgetArticleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBudgetArticle extends CreateRecord
{
    protected static string $resource = BudgetArticleResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un article budgétaire';
    }
}
