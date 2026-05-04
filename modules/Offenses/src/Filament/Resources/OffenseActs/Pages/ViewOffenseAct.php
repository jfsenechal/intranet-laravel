<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Pages;

use AcMarche\Offenses\Filament\Resources\OffenseActs\OffenseActResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewOffenseAct extends ViewRecord
{
    #[Override]
    protected static string $resource = OffenseActResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon(Heroicon::Pencil),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
