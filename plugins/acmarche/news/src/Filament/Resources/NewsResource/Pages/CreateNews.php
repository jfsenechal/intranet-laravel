<?php

namespace AcMarche\News\Filament\Resources\NewsResource\Pages;

use AcMarche\News\Events\NewsProcessed;
use AcMarche\News\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une actualité';
    }

    protected function afterCreate(): void
    {
        NewsProcessed::dispatch($this->record);
    }

    protected function getRedirectUrl22(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('complete') && $resource::canView($this->getRecord())) {
            return $resource::getUrl('complete', ['record' => $this->getRecord()]);
        }

        return $resource::getUrl('index');
    }

}
