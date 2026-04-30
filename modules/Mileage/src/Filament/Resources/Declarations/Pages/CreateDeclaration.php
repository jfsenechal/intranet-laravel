<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateDeclaration extends CreateRecord
{
    #[Override]
    protected static string $resource = DeclarationResource::class;
}
