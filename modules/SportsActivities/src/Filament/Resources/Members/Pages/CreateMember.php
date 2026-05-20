<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Members\Pages;

use AcMarche\SportsActivities\Filament\Resources\Members\MemberResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateMember extends CreateRecord
{
    #[Override]
    protected static string $resource = MemberResource::class;

    public function getTitle(): string
    {
        return 'Nouveau sportif';
    }
}
