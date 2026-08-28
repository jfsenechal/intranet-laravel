<?php

declare(strict_types=1);

namespace AcMarche\Note\Filament\Resources\Notes\Pages;

use AcMarche\Note\Filament\Resources\Notes\NoteResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditNote extends EditRecord
{
    #[Override]
    protected static string $resource = NoteResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
