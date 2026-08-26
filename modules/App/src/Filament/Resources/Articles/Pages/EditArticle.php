<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Articles\Pages;

use AcMarche\App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditArticle extends EditRecord
{
    #[Override]
    protected static string $resource = ArticleResource::class;

    public function getTitle(): string
    {
        return "Modifier l'article";
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
