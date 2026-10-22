<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contacts\Pages;

use AcMarche\Hrm\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ViewContact extends ViewRecord
{
    #[Override]
    protected static string $resource = ContactResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->first_name.' '.$this->record->last_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('Retour à la liste')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->url(ContactResource::getUrl('index')),
            EditAction::make()->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
