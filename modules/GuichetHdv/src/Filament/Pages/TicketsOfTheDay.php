<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Ticket\TicketResource;
use AcMarche\GuichetHdv\Models\Ticket;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
