<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages;

use AcMarche\Ad\Ai\ClassifiedAdModerator;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\Concerns\ModeratesClassifiedAd;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditClassifiedAd extends EditRecord
{
    use ModeratesClassifiedAd;

    #[Override]
    protected static string $resource = ClassifiedAdResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->hasModeratedContentChanged($data)) {
            $this->ensureAdRespectsCodeOfConduct($data);
        }

        return $data;
    }

    /**
     * Only the title, text and attachments are moderated, so changing the
     * category or end date alone does not need another AI check.
     *
     * The text is compared as plain text: the rich editor rewrites the stored
     * HTML when the form loads, so the raw markup differs even when nothing
     * was edited.
     *
     * @param  array<string, mixed>  $data
     */
    private function hasModeratedContentChanged(array $data): bool
    {
        $record = $this->getRecord();

        return (string) ($data['name'] ?? '') !== (string) $record->name
            || ClassifiedAdModerator::plainText((string) ($data['content'] ?? '')) !== ClassifiedAdModerator::plainText((string) $record->content)
            || $this->sortedPaths($data['medias'] ?? []) !== $this->sortedPaths($record->medias ?? []);
    }

    /**
     * @return list<string>
     */
    private function sortedPaths(mixed $paths): array
    {
        $paths = array_values(array_filter((array) $paths, is_string(...)));
        sort($paths);

        return $paths;
    }
}
