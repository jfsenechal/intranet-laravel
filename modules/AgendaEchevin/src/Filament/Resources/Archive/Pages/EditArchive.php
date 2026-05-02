<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Archive\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Archive\ArchiveResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditArchive extends EditRecord
{
    #[Override]
    protected static string $resource = ArchiveResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->title ?? 'Archive';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
