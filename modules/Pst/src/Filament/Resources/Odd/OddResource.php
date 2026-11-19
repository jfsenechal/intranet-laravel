<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Odd;

use AcMarche\Pst\Filament\Concerns\InteractsWithScoutGlobalSearch;
use AcMarche\Pst\Filament\Resources\Odd\Pages\CreateOdd;
use AcMarche\Pst\Filament\Resources\Odd\Pages\EditOdd;
use AcMarche\Pst\Filament\Resources\Odd\Pages\ListOdds;
use AcMarche\Pst\Filament\Resources\Odd\Pages\ViewOdd;
use AcMarche\Pst\Filament\Resources\Odd\Schemas\OddForm;
use AcMarche\Pst\Filament\Resources\Odd\Tables\OddTables;
use AcMarche\Pst\Models\Odd;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

// todo try https://github.com/LaravelDaily/FilamentExamples-Projects/tree/main/tables/table-reorderable-position
final class OddResource extends Resource
{
    use InteractsWithScoutGlobalSearch;

    #[Override]
    protected static ?string $model = Odd::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-trees';

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    protected static ?string $navigationLabel = 'Développement durable (ODD)';

    public static function getModelLabel(): string
    {
        return 'Objectif de développement durable (ODD)';
    }

    public static function form(Schema $schema): Schema
    {
        return OddForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OddTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOdds::route('/'),
            'create' => CreateOdd::route('/create'),
            'view' => ViewOdd::route('/{record}'),
            'edit' => EditOdd::route('/{record}/edit'),
        ];
    }
}
