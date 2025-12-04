<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticleResource\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetArticle extends CreateRecord
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
