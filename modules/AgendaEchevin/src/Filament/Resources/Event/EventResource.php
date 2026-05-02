<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Event;

use AcMarche\AgendaEchevin\Filament\Resources\Event\Pages\CreateEvent;
use AcMarche\AgendaEchevin\Filament\Resources\Event\Pages\EditEvent;
use AcMarche\AgendaEchevin\Filament\Resources\Event\Pages\ListEvents;
use AcMarche\AgendaEchevin\Filament\Resources\Event\Pages\ViewEvent;
use AcMarche\AgendaEchevin\Filament\Resources\Event\Schemas\EventForm;
use AcMarche\AgendaEchevin\Filament\Resources\Event\Tables\EventTables;
use AcMarche\AgendaEchevin\Models\Event;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class EventResource extends Resource
{
    #[Override]
    protected static ?string $model = Event::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar';
    }

    public static function getNavigationLabel(): string
    {
        return 'Événements';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Agenda Échevin';
    }

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
            'view' => ViewEvent::route('/{record}'),
        ];
    }
}
