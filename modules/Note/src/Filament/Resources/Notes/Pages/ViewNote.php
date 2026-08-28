<?php

declare(strict_types=1);

namespace AcMarche\Note\Filament\Resources\Notes\Pages;

use AcMarche\Note\Filament\Resources\Notes\NoteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewNote extends ViewRecord
{
    #[Override]
    protected static string $resource = NoteResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
