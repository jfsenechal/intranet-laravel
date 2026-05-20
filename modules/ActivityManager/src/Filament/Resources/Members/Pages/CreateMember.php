<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\Pages;

use AcMarche\ActivityManager\Filament\Resources\Members\MembersResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateMember extends CreateRecord
{
    #[Override]
    protected static string $resource = MembersResource::class;

    public function getTitle(): string
    {
        return 'Nouveau membre';
    }
}
