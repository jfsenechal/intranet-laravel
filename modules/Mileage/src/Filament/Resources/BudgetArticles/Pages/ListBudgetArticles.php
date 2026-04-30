<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticles\BudgetArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListBudgetArticles extends ListRecords
{
    #[Override]
    protected static string $resource = BudgetArticleResource::class;

    public function getTitle(): string
    {
        return 'Liste des articles budgétaires';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un article budgétaire')
                ->icon('tabler-plus'),
        ];
    }
}
