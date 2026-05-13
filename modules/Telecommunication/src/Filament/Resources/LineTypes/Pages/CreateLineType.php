<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages;

use AcMarche\Telecommunication\Filament\Resources\LineTypes\LineTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateLineType extends CreateRecord
{
    #[Override]
    protected static string $resource = LineTypeResource::class;

    public function getTitle(): string
    {
        return 'Nouveau type de ligne';
    }
}
