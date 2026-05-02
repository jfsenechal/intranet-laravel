<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Recipient;

use AcMarche\AgendaEchevin\Filament\Resources\Recipient\Pages\CreateRecipient;
use AcMarche\AgendaEchevin\Filament\Resources\Recipient\Pages\EditRecipient;
use AcMarche\AgendaEchevin\Filament\Resources\Recipient\Pages\ListRecipients;
use AcMarche\AgendaEchevin\Filament\Resources\Recipient\Pages\ViewRecipient;
use AcMarche\AgendaEchevin\Filament\Resources\Recipient\Schemas\RecipientForm;
use AcMarche\AgendaEchevin\Filament\Resources\Recipient\Tables\RecipientTables;
use AcMarche\AgendaEchevin\Models\Recipient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class RecipientResource extends Resource
{
    #[Override]
    protected static ?string $model = Recipient::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationLabel(): string
    {
        return 'Destinataires';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Agenda Échevin';
    }

    public static function form(Schema $schema): Schema
    {
        return RecipientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecipientTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecipients::route('/'),
            'create' => CreateRecipient::route('/create'),
            'edit' => EditRecipient::route('/{record}/edit'),
            'view' => ViewRecipient::route('/{record}'),
        ];
    }
}
