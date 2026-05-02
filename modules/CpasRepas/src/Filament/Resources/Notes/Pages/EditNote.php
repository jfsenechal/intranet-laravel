<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Notes\Pages;

use AcMarche\CpasRepas\Filament\Resources\Notes\NoteResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditNote extends EditRecord
{
    #[Override]
    protected static string $resource = NoteResource::class;

    public function getTitle(): string
    {
        return 'Edit note';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
