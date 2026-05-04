<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes\Pages;

use AcMarche\MealDelivery\Filament\Resources\Notes\NoteResource;
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
