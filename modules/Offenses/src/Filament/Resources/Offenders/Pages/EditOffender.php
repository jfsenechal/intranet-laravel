<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Pages;

use AcMarche\Offenses\Filament\Resources\Offenders\OffenderResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditOffender extends EditRecord
{
    #[Override]
    protected static string $resource = OffenderResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->first_name.' '.$this->record->last_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
