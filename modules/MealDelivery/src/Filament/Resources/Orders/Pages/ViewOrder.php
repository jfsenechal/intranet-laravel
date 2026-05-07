<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewOrder extends ViewRecord
{
    #[Override]
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        /** @var Order $order */
        $order = $this->record;
        $client = $order->client;
        $week = $order->week;

        return sprintf(
            'Commande %d pour %s %s, semaine du %s',
            $order->id,
            $client->last_name,
            $client->first_name,
            $week->formattedFirstDay(),
        );
    }

    protected function getHeaderActions(): array
    {
        /** @var Order $order */
        $order = $this->record;

        return [
            ActionGroup::make([
                Action::make('back_to_client')
                    ->label('Retour au client')
                    ->icon(Heroicon::ArrowLeft)
                    ->visible(fn (): bool => $order->client !== null)
                    ->url(fn (): string => ClientResource::getUrl('edit', ['record' => $order->client_id])),

                Action::make('back_to_week')
                    ->label('Retour à la semaine')
                    ->icon(Heroicon::ArrowLeft)
                    ->visible(fn (): bool => $order->week !== null)
                    ->url(fn (): string => WeekResource::getUrl('view', ['record' => $order->week_id])),
            ])
                ->label('Retour')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->button(),

            EditAction::make()
                ->icon(Heroicon::Pencil),

            DeleteAction::make()
                ->label('Supprimer la commande')
                ->icon(Heroicon::Trash),
        ];
    }
}
