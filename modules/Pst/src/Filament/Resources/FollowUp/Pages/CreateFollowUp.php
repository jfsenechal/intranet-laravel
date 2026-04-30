<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\FollowUp\Pages;

use AcMarche\Pst\Filament\Resources\FollowUp\FollowUpResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateFollowUp extends CreateRecord
{
    #[Override]
    protected static string $resource = FollowUpResource::class;
}
