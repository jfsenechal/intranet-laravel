<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Filament\Resources\Notes\NoteResource;
use AcMarche\MealDelivery\Models\Note;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewNote extends ViewRecord
{
    #[Override]
    protected static string $resource = NoteResource::class;

    public function getTitle(): string
    {
        /** @var Note $note */
        $note = $this->record;

        return 'Note du '.($note->note_date?->format('d/m/Y') ?? '');
    }

    protected function getHeaderActions(): array
    {
        /** @var Note $note */
        $note = $this->record;

        return [
            Action::make('back_to_client')
                ->label('Retour au client')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->visible(fn (): bool => $note->client_id !== null)
                ->url(fn (): string => ClientResource::getUrl('view', ['record' => $note->client_id])),

            EditAction::make()
                ->icon(Heroicon::Pencil),

            DeleteAction::make()
                ->icon(Heroicon::Trash)
                ->successRedirectUrl(fn (): string => $note->client_id !== null
                    ? ClientResource::getUrl('view', ['record' => $note->client_id])
                    : NoteResource::getUrl()),
        ];
    }
}
