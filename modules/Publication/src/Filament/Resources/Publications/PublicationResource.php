<?php

declare(strict_types=1);

namespace AcMarche\Publication\Filament\Resources\Publications;

use AcMarche\Publication\Filament\Resources\Publications\Pages\CreatePublication;
use AcMarche\Publication\Filament\Resources\Publications\Pages\EditPublication;
use AcMarche\Publication\Filament\Resources\Publications\Pages\ListPublications;
use AcMarche\Publication\Filament\Resources\Publications\Pages\ViewPublication;
use AcMarche\Publication\Filament\Resources\Publications\Schemas\PublicationForm;
use AcMarche\Publication\Filament\Resources\Publications\Tables\PublicationTables;
use AcMarche\Publication\Models\Publication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class PublicationResource extends Resource
{
    #[Override]
    protected static ?string $model = Publication::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-newspaper';

    #[Override]
    protected static ?string $navigationLabel = 'Publications';

    #[Override]
    protected static ?string $modelLabel = 'Publication';

    #[Override]
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
            'index' => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'view' => ViewPublication::route('/{record}'),
            'edit' => EditPublication::route('/{record}/edit'),
        ];
    }
}
