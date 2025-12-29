<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticles\BudgetArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditBudgetArticle extends EditRecord
{
    protected static string $resource = BudgetArticleResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
