<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticle\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticle\BudgetArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditBudgetArticle extends EditRecord
{
    protected static string $resource = BudgetArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return $this->record->name;
    }
}
