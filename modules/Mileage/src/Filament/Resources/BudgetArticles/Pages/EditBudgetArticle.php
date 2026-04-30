<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticles\BudgetArticleResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditBudgetArticle extends EditRecord
{
    #[Override]
    protected static string $resource = BudgetArticleResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
