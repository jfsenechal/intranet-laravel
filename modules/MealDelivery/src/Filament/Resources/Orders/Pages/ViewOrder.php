<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Models\Order;
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
            'Repas pour %s %s, semaine du %s',
            $client->last_name,
            $client->first_name,
            $week->formattedFirstDay(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::Pencil),

            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
