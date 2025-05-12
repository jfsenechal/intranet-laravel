<?php

namespace AcMarche\App\Filament;

trait RedirectTrait
{
    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        return $resource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
