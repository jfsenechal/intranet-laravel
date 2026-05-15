<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket;

use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\CreateTicket;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\EditTicket;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\ListTicket;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\ViewTicket;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Schemas\TicketForm;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Tables\TicketTables;
use AcMarche\GuichetHdv\Models\Ticket;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class TicketResource extends Resource
{
    #[Override]
    protected static ?string $model = Ticket::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-ticket';
    }

    public static function getNavigationLabel(): string
    {
        return 'Tickets';
    }

    public static function form(Schema $schema): Schema
    {
        return TicketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTicket::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
            'view' => ViewTicket::route('/{record}'),
        ];
    }
}
