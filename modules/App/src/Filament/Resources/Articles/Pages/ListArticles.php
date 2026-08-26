<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Articles\Pages;

use AcMarche\App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListArticles extends ListRecords
{
    #[Override]
    protected static string $resource = ArticleResource::class;

    public function getTitle(): string
    {
        return 'Articles';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
