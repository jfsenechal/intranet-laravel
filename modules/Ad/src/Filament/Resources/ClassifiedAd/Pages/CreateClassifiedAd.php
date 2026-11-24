<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages;

use AcMarche\Ad\Events\ClassifiedAdProcessed;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\Concerns\ModeratesClassifiedAd;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateClassifiedAd extends CreateRecord
{
    use ModeratesClassifiedAd;

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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->ensureAdRespectsCodeOfConduct($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        event(new ClassifiedAdProcessed($this->record));
    }
}
