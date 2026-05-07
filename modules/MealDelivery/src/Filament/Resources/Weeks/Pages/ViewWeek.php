<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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
                ->modalHeading('Sélectionner un client')
                ->modalSubmitActionLabel('Continuer')
                ->schema([
                    Select::make('client_id')
                        ->label('Client')
                        ->options(fn (): array => Client::query()
                            ->where('is_active', true)
                            ->orderBy('last_name')
                            ->orderBy('first_name')
                            ->get()
                            ->mapWithKeys(fn (Client $client): array => [
                                $client->id => $client->last_name.' '.$client->first_name,
                            ])
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(fn (array $data): mixed => $this->redirect(CreateOrder::getUrl([
                    'week_id' => $this->record->id,
                    'client_id' => $data['client_id'],
                ]))),

            EditAction::make()
                ->icon(Heroicon::Pencil),

            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
