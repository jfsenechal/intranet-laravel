<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Pages;

use AcMarche\Offenses\Filament\Resources\OffenseActs\OffenseActResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditOffenseAct extends EditRecord
{
    #[Override]
    protected static string $resource = OffenseActResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
