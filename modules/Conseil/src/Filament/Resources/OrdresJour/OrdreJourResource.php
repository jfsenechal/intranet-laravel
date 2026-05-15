<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\OrdresJour;

use AcMarche\Conseil\Filament\Resources\OrdresJour\Pages\CreateOrdreJour;
use AcMarche\Conseil\Filament\Resources\OrdresJour\Pages\EditOrdreJour;
use AcMarche\Conseil\Filament\Resources\OrdresJour\Pages\ListOrdresJour;
use AcMarche\Conseil\Filament\Resources\OrdresJour\Pages\ViewOrdreJour;
use AcMarche\Conseil\Filament\Resources\OrdresJour\Schemas\OrdreJourForm;
use AcMarche\Conseil\Filament\Resources\OrdresJour\Tables\OrdresJourTable;
use AcMarche\Conseil\Models\OrdreJour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class OrdreJourResource extends Resource
{
    #[Override]
    protected static ?string $model = OrdreJour::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Ordres du jour';

    #[Override]
    protected static ?string $modelLabel = 'ordre du jour';

    #[Override]
    protected static ?string $pluralModelLabel = 'ordres du jour';

    #[Override]
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nom',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return OrdreJourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdresJourTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrdresJour::route('/'),
            'create' => CreateOrdreJour::route('/create'),
            'view' => ViewOrdreJour::route('/{record}'),
            'edit' => EditOrdreJour::route('/{record}/edit'),
        ];
    }
}
