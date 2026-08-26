<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Articles\Pages;

use AcMarche\App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewArticle extends ViewRecord
{
    #[Override]
    protected static string $resource = ArticleResource::class;

    public function getTitle(): string
    {
        return "Détail de l'article";
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon(Heroicon::Pencil),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
