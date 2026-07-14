<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewWeek extends ViewRecord
{
    #[Override]
    protected static string $resource = WeekResource::class;

    public function getTitle(): string
    {
        return 'Semaine du '.$this->record->formattedFirstDay();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addOrder')
                ->label('Ajouter une commande')
                ->icon(Heroicon::Plus)
                ->url(fn (): string => WeekResource::getUrl('add-order', ['record' => $this->record->id])),

            EditAction::make()
                ->icon(Heroicon::Pencil),

            DeleteAction::make()
                ->label('Supprimer la semaine')
                ->icon(Heroicon::Trash),
        ];
    }
}
