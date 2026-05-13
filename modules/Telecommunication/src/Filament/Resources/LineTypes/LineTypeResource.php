<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\LineTypes;

use AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages\CreateLineType;
use AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages\EditLineType;
use AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages\ListLineTypes;
use AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages\ViewLineType;
use AcMarche\Telecommunication\Filament\Resources\LineTypes\Schemas\LineTypeForm;
use AcMarche\Telecommunication\Filament\Resources\LineTypes\Tables\LineTypesTable;
use AcMarche\Telecommunication\Models\LineType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class LineTypeResource extends Resource
{
    #[Override]
    protected static ?string $model = LineType::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Types de ligne';

    #[Override]
    protected static ?string $modelLabel = 'type de ligne';

    #[Override]
    protected static ?string $pluralModelLabel = 'types de ligne';

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
        return LineTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LineTypesTable::configure($table);
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
            'index' => ListLineTypes::route('/'),
            'create' => CreateLineType::route('/create'),
            'view' => ViewLineType::route('/{record}'),
            'edit' => EditLineType::route('/{record}/edit'),
        ];
    }
}
