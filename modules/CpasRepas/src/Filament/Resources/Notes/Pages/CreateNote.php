<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Notes\Pages;

use AcMarche\CpasRepas\Filament\Resources\Notes\NoteResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateNote extends CreateRecord
{
    #[Override]
    protected static string $resource = NoteResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Add note';
    }
}
