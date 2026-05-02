<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Event\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Event\EventResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditEvent extends EditRecord
{
    #[Override]
    protected static string $resource = EventResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
