<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Tags;

use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\CreateTag;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\EditTag;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\ListTags;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\ViewTag;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Schemas\TagForm;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Tables\TagsTable;
use AcMarche\CpasLibrary\Models\Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class TagResource extends Resource
{
    #[Override]
    protected static ?string $model = Tag::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Bibliothèque';

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Tags';

    #[Override]
    protected static ?string $modelLabel = 'tag';

    #[Override]
    protected static ?string $pluralModelLabel = 'tags';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'view' => ViewTag::route('/{record}'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
