<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Schemas\GuestReservationForm;
use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
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

        $orderedDays = self::orderedDays($order);

        return [
            ActionGroup::make([
                Action::make('back_to_client')
                    ->label('Retour au client')
                    ->icon(Heroicon::ArrowLeft)
                    ->visible(fn (): bool => $order->client !== null)
                    ->url(fn (): string => ClientResource::getUrl('view', ['record' => $order->client_id])),

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

            Action::make('add_guest_reservation')
                ->label('Ajouter un repas invité')
                ->icon(Heroicon::UserGroup)
                ->color('success')
                ->visible(fn (): bool => $order->client !== null
                    && (bool) $order->client->use_cafeteria
                    && $orderedDays !== [])
                ->modalHeading(fn (): string => 'Repas invités de '.mb_trim(
                    $order->client->last_name.' '.$order->client->first_name,
                ))
                ->modalSubmitActionLabel('Enregistrer')
                ->schema([
                    Hidden::make('client_id')
                        ->default($order->client_id),

                    Select::make('date')
                        ->label('Date du repas')
                        ->helperText('Repas de midi, parmi les jours commandés.')
                        ->options($orderedDays)
                        ->default(array_key_first($orderedDays))
                        ->required()
                        ->rule(GuestReservationForm::uniquePerClientAndDate()),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('menu1_count')
                                ->label('Menu 1')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->rule(GuestReservationForm::atLeastOneMeal()),

                            TextInput::make('menu2_count')
                                ->label('Menu 2')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),

                    Textarea::make('notes')
                        ->label('Remarques')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($order): void {
                    GuestReservation::create([
                        'client_id' => $order->client_id,
                        'date' => $data['date'],
                        'menu1_count' => (int) $data['menu1_count'],
                        'menu2_count' => (int) $data['menu2_count'],
                        'notes' => $data['notes'] ?? null,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Repas invités enregistrés.')
                        ->send();
                }),

            EditAction::make()
                ->icon(Heroicon::Pencil),

            DeleteAction::make()
                ->label('Supprimer la commande')
                ->icon(Heroicon::Trash)
                ->successRedirectUrl(fn (): string => $order->client_id !== null
                    ? ClientResource::getUrl('view', ['record' => $order->client_id])
                    : WeekResource::getUrl('view', ['record' => $order->week_id])),
        ];
    }

    /**
     * Days of this order on which something was actually ordered, keyed by date.
     * A `Meal` row exists for every day of the week even when nothing is ordered,
     * and those placeholders must never be offered as a guest meal date.
     *
     * @return array<string, string>
     */
    private static function orderedDays(Order $order): array
    {
        return $order->meals
            ->loadMissing('menus')
            ->filter(fn (Meal $meal): bool => $meal->date !== null
                && $meal->menus
                    ->whereIn('position', [1, 2])
                    ->where('quantity', '>', 0)
                    ->isNotEmpty())
            ->sortBy(fn (Meal $meal): string => $meal->date->format('Y-m-d'))
            ->mapWithKeys(fn (Meal $meal): array => [
                $meal->date->format('Y-m-d') => Str::title($meal->date->translatedFormat('l j F Y'))
                    .($meal->at_cafeteria ? ' — cafétéria' : ''),
            ])
            ->all();
    }
}
