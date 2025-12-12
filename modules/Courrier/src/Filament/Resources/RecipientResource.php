<?php

namespace AcMarche\Courrier\Filament\Resources;

use AcMarche\Courrier\Filament\Resources\RecipientResource\Pages;
use AcMarche\Courrier\Filament\Resources\RecipientResource\Schema\RecipientForm;
use AcMarche\Courrier\Filament\Resources\RecipientResource\Tables\RecipientTables;
use AcMarche\Courrier\Models\Recipient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class RecipientResource extends Resource
{
    protected static ?string $model = Recipient::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): ?string
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
            'index' => Pages\ListRecipients::route('/'),
            'create' => Pages\CreateRecipient::route('/create'),
            'edit' => Pages\EditRecipient::route('/{record}/edit'),
        ];
    }
}
