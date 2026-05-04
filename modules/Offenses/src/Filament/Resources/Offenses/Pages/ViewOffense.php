<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Pages;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewOffense extends ViewRecord
{
    #[Override]
    protected static string $resource = OffenseResource::class;

    public function getTitle(): string
    {
        $offender = $this->record->offender;
        $name = $offender ? mb_trim($offender->last_name.' '.$offender->first_name) : '—';

        return 'Sanction · '.$name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon(Heroicon::Pencil),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
