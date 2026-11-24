<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages;

use AcMarche\Ad\Ai\ClassifiedAdModerator;
use AcMarche\Ad\Events\ClassifiedAdProcessed;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateClassifiedAd extends CreateRecord
{
    #[Override]
    protected static string $resource = ClassifiedAdResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une petite annonce';
    }

    /**
     * Refuses the ad when the AI finds that its text or attachments break the
     * code of conduct shown above the form.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $verdict = app(ClassifiedAdModerator::class)->moderate(
            (string) ($data['name'] ?? ''),
            (string) ($data['content'] ?? ''),
            array_values(array_filter((array) ($data['medias'] ?? []), is_string(...))),
        );

        if (! $verdict->isCompliant) {
            Notification::make()
                ->title('Annonce refusée : elle ne respecte pas les règles de bonne conduite')
                ->body(implode("\n", $verdict->reasons) ?: null)
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        event(new ClassifiedAdProcessed($this->record));
    }
}
