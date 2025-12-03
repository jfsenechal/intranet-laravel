<?php

namespace AcMarche\Publication\Filament\Resources;

use AcMarche\Publication\Filament\Resources\PublicationResource\Pages;
use AcMarche\Publication\Filament\Resources\PublicationResource\Schema\PublicationForm;
use AcMarche\Publication\Filament\Resources\PublicationResource\Tables\PublicationTables;
use AcMarche\Publication\Models\Publication;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;
    protected static ?int $navigationSort = 1;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Publications';

    protected static ?string $modelLabel = 'Publication';

    protected static ?string $pluralModelLabel = 'Publications';

    public static function form(Schema $schema): Schema
    {
        return PublicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicationTables::configure($table);
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
            'index' => Pages\ListPublications::route('/'),
            'create' => Pages\CreatePublication::route('/create'),
            'view' => Pages\ViewPublication::route('/{record}'),
            'edit' => Pages\EditPublication::route('/{record}/edit'),
        ];
    }
}
