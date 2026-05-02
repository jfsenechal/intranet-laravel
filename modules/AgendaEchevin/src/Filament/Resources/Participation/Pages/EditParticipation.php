<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Participation\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Participation\ParticipationResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditParticipation extends EditRecord
{
    #[Override]
    protected static string $resource = ParticipationResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->event->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
