<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\RequestsBy;

use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\CreateRequestBy;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\EditRequestBy;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\ListRequestsBy;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\ViewRequestBy;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Schemas\RequestByForm;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Tables\RequestsByTable;
use AcMarche\StreetWatch\Models\RequestBy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class RequestByResource extends Resource
{
    #[Override]
    protected static ?string $model = RequestBy::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Travail de rue';

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Demandeurs';

    #[Override]
    protected static ?string $modelLabel = 'demandeur';

    #[Override]
    protected static ?string $pluralModelLabel = 'demandeurs';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RequestByForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RequestsByTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRequestsBy::route('/'),
            'create' => CreateRequestBy::route('/create'),
            'view' => ViewRequestBy::route('/{record}'),
            'edit' => EditRequestBy::route('/{record}/edit'),
        ];
    }
}
