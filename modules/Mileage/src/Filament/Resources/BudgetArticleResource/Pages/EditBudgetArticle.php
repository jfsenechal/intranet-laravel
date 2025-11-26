<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticleResource\Pages;

use AcMarche\Mileage\Filament\Resources\BudgetArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetArticle extends EditRecord
{
    protected static string $resource = BudgetArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
