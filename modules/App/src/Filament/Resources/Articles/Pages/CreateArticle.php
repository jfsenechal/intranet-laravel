<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Articles\Pages;

use AcMarche\App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateArticle extends CreateRecord
{
    #[Override]
    protected static string $resource = ArticleResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un article';
    }
}
