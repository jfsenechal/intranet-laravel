<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Recipients;

use AcMarche\Courrier\Filament\Resources\Recipients\Pages\CreateRecipient;
use AcMarche\Courrier\Filament\Resources\Recipients\Pages\EditRecipient;
use AcMarche\Courrier\Filament\Resources\Recipients\Pages\ListRecipients;
use AcMarche\Courrier\Filament\Resources\Recipients\Schemas\RecipientForm;
use AcMarche\Courrier\Filament\Resources\Recipients\Tables\RecipientTables;
use AcMarche\Courrier\Models\Recipient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class RecipientResource extends Resource
{
    #[Override]
    protected static ?string $model = Recipient::class;

    #[Override]
    protected static ?int $navigationSort = 4;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Paramètres';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationLabel(): string
    {
        return 'Destinataires';
    }

    public static function getModelLabel(): string
    {
        return 'destinataire';
    }

    public static function getPluralModelLabel(): string
    {
        return 'destinataires';
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
        ];
    }
}
