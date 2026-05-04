<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Pages;

use AcMarche\Offenses\Filament\Resources\Offenders\OffenderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewOffender extends ViewRecord
{
    #[Override]
    protected static string $resource = OffenderResource::class;

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon(Heroicon::Pencil),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
