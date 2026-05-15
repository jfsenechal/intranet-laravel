<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Ticket\TicketResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

final class CreateTicket extends CreateRecord
{
    #[Override]
    protected static string $resource = TicketResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un ticket';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_add'] = Auth::user()?->username ?? Auth::user()?->name ?? '';

        return $data;
    }
}
