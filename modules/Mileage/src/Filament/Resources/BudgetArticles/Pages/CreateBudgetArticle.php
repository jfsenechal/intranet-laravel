<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticles\BudgetArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateBudgetArticle extends CreateRecord
{
    #[Override]
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
