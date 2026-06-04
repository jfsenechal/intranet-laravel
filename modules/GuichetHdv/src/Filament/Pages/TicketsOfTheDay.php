<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Pages;

use AcMarche\GuichetHdv\Enums\RolesEnum;
use AcMarche\GuichetHdv\Events\TicketAssigned;
use AcMarche\GuichetHdv\Events\TicketCancelled;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\TicketResource;
use AcMarche\GuichetHdv\Models\Office;
use AcMarche\GuichetHdv\Models\Ticket;
use AcMarche\GuichetHdv\Notifications\TicketAssignedPush;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Livewire\Attributes\On;
use Override;

final class TicketsOfTheDay extends Page
{
    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::CalendarDays;

    #[Override]
    protected static ?string $navigationLabel = 'Tickets du jour';

    #[Override]
    protected static ?int $navigationSort = 0;

    #[Override]
    protected string $view = 'guichet-hdv::filament.pages.tickets-of-the-day';

    public function getTitle(): string
    {
        return 'Tickets du jour';
    }

    /**
     * Tickets created today that are waiting for a counter (no office assigned).
     *
     * @return Collection<int, Ticket>
     */
    public function getPendingTickets(): Collection
    {
        return $this->baseQuery()
            ->whereNull('office_id')
            ->get();
    }

    /**
     * Tickets created today that are currently being handled at a counter.
     *
     * @return Collection<int, Ticket>
     */
    public function getProcessingTickets(): Collection
    {
        return $this->baseQuery()
            ->whereNotNull('office_id')
            ->with('office')
            ->get();
    }

    /**
     * Assign a counter to a pending ticket (sets office, assignee and date).
     */
    public function assignOfficeAction(): Action
    {
        return Action::make('assignOffice')
            ->label('Assigner un guichet')
            ->icon('heroicon-o-building-office-2')
            ->color('info')
            ->size(Size::Small)
            ->modalHeading('Assigner un guichet')
            ->visible(fn (): bool => $this->userIsGuichetAgent())
            ->schema([
                Select::make('office_id')
                    ->label('Guichet')
                    ->options(fn (): array => Office::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
            ])
            ->action(function (array $arguments, array $data): void {
                $ticket = Ticket::query()->find($arguments['ticket'] ?? null);

                if (! $ticket instanceof Ticket) {
                    return;
                }

                $ticket->update([
                    'office_id' => $data['office_id'],
                    'assigned_by' => $this->currentUsername(),
                    'assigned_date' => now(),
                ]);

                $ticket->load('office');

                TicketAssigned::dispatch($ticket);
                $this->sendAssignmentPush($ticket);

                Notification::make()
                    ->title('Guichet assigné')
                    ->success()
                    ->send();
            });
    }

    /**
     * Cancel a ticket by archiving it.
     */
    public function cancelTicketAction(): Action
    {
        return Action::make('cancelTicket')
            ->label('Archiver')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->size(Size::Small)
            ->requiresConfirmation()
            ->modalHeading('Archiver le ticket')
            ->modalDescription('Le ticket sera archivé.')
            ->visible(fn (): bool => $this->userIsGuichetAgent())
            ->action(function (array $arguments): void {
                $ticket = Ticket::query()->find($arguments['ticket'] ?? null);

                if (! $ticket instanceof Ticket) {
                    return;
                }

                $ticket->update(['archive' => true]);

                TicketCancelled::dispatch($ticket);

                Notification::make()
                    ->title('Ticket archivé')
                    ->success()
                    ->send();
            });
    }

    /**
     * Re-render the page (and re-run getViewData) when a ticket changes elsewhere.
     * The browser dispatches this Livewire event after receiving an Echo broadcast.
     */
    #[On('tickets-updated')]
    public function refreshTickets(): void
    {
        // Intentionally empty: Livewire re-renders after handling the event.
    }

    /**
     * Persist the browser's Web Push subscription for the current user.
     *
     * @param  array{endpoint?: string, keys?: array{p256dh?: string, auth?: string}}  $subscription
     */
    public function storePushSubscription(array $subscription): void
    {
        $endpoint = $subscription['endpoint'] ?? null;

        if ($endpoint === null) {
            return;
        }

        Auth::user()?->updatePushSubscription(
            $endpoint,
            $subscription['keys']['p256dh'] ?? null,
            $subscription['keys']['auth'] ?? null,
        );
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createTicket')
                ->label('Ajouter un ticket')
                ->icon('tabler-plus')
                ->url(TicketResource::getUrl('create')),
        ];
    }

    /**
     * @return array{pending: Collection<int, Ticket>, processing: Collection<int, Ticket>}
     */
    #[Override]
    protected function getViewData(): array
    {
        return [
            'pending' => $this->getPendingTickets(),
            'processing' => $this->getProcessingTickets(),
        ];
    }

    /**
     * Send a Web Push notification to guichet agents (used for closed-tab delivery).
     */
    private function sendAssignmentPush(Ticket $ticket): void
    {
        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                RolesEnum::ROLE_GUICHET_AGENT->value,
                RolesEnum::ROLE_GUICHET->value,
            ]))
            ->when(Auth::id(), fn (Builder $query, int|string $id) => $query->whereKeyNot($id))
            ->get();

        if ($recipients->isNotEmpty()) {
            NotificationFacade::send($recipients, new TicketAssignedPush($ticket));
        }
    }

    private function userIsGuichetAgent(): bool
    {
        return Auth::user()?->hasRole(RolesEnum::ROLE_GUICHET_AGENT->value) ?? false;
    }

    private function currentUsername(): string
    {
        return Auth::user()?->username ?? Auth::user()?->name ?? '';
    }

    /**
     * @return Builder<Ticket>
     */
    private function baseQuery(): Builder
    {
        return Ticket::query()
            ->whereDate(Ticket::CREATED_AT, today())
            ->where('archive', false)
            ->orderBy(Ticket::CREATED_AT);
    }
}
