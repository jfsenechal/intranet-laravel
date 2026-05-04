<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Pages;

use AcMarche\Offenses\Filament\Resources\Offenders\OffenderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditOffender extends EditRecord
{
    #[Override]
    protected static string $resource = OffenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
