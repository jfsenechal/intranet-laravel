<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages;

use AcMarche\StreetWatch\Filament\Resources\RequestsBy\RequestByResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateRequestBy extends CreateRecord
{
    #[Override]
    protected static string $resource = RequestByResource::class;

    public function getTitle(): string
    {
        return 'Nouveau demandeur';
    }
}
