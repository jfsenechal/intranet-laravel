<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Pages;

use AcMarche\Hrm\Filament\Resources\Teleworks\TeleworkResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ViewTelework extends ViewRecord
{
    #[Override]
    protected static string $resource = TeleworkResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Télétravail - '.$this->record->user_add;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('managerValidate')
                ->label('Validation du directeur')
                ->icon(Heroicon::CheckBadge)
                ->url(fn () => ManagerValidateTelework::getUrl(['record' => $this->record]))
                ->visible(fn (): bool => ManagerValidateTelework::canAccess(['record' => $this->record])),
            Action::make('hrValidate')
                ->label('Traitement GRH')
                ->icon(Heroicon::ClipboardDocumentCheck)
                ->url(fn () => HrValidateTelework::getUrl(['record' => $this->record]))
                ->visible(fn (): bool => HrValidateTelework::canAccess(['record' => $this->record])),
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
