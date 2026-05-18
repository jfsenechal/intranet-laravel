<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Destinataires;

use AcMarche\College\Filament\Resources\Destinataires\Pages\CreateDestinataire;
use AcMarche\College\Filament\Resources\Destinataires\Pages\EditDestinataire;
use AcMarche\College\Filament\Resources\Destinataires\Pages\ListDestinataires;
use AcMarche\College\Filament\Resources\Destinataires\Pages\ViewDestinataire;
use AcMarche\College\Filament\Resources\Destinataires\Schemas\DestinataireForm;
use AcMarche\College\Filament\Resources\Destinataires\Tables\DestinatairesTable;
use AcMarche\College\Models\Destinataire;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class DestinataireResource extends Resource
{
    #[Override]
    protected static ?string $model = Destinataire::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Destinataires';

    #[Override]
    protected static ?string $modelLabel = 'destinataire';

    #[Override]
    protected static ?string $pluralModelLabel = 'destinataires';

    #[Override]
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nom',
            'prenom',
            'email',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DestinataireForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DestinatairesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDestinataires::route('/'),
            'create' => CreateDestinataire::route('/create'),
            'view' => ViewDestinataire::route('/{record}'),
            'edit' => EditDestinataire::route('/{record}/edit'),
        ];
    }
}
