<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Events\NewsProcessed;
use AcMarche\News\Filament\Resources\News\NewsResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateNews extends CreateRecord
{
    #[Override]
    protected static string $resource = NewsResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une actualité';
    }

    protected function afterCreate(): void
    {
        event(new NewsProcessed($this->record));
    }
}
