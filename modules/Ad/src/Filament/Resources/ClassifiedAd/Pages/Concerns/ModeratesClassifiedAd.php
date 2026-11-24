<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\Concerns;

use AcMarche\Ad\Ai\ClassifiedAdModerator;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

trait ModeratesClassifiedAd
{
    /**
     * Stops the save when the AI finds that the ad's text or attachments break
     * the code of conduct shown above the form, and tells the author why.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Halt
     */
    private function ensureAdRespectsCodeOfConduct(array $data): void
    {
        $verdict = app(ClassifiedAdModerator::class)->moderate(
            (string) ($data['name'] ?? ''),
            (string) ($data['content'] ?? ''),
            array_values(array_filter((array) ($data['medias'] ?? []), is_string(...))),
        );

        if ($verdict->isCompliant) {
            return;
        }

        Notification::make()
            ->title('Annonce refusée : elle ne respecte pas les règles de bonne conduite')
            ->body(implode("\n", $verdict->reasons) ?: null)
            ->danger()
            ->persistent()
            ->send();

        $this->halt();
    }
}
