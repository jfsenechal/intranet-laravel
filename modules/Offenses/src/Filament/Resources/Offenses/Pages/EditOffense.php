<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Pages;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditOffense extends EditRecord
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
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
