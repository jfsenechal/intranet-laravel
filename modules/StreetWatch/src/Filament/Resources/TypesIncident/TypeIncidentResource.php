<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\TypesIncident;

use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages\CreateTypeIncident;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages\EditTypeIncident;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages\ListTypesIncident;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages\ViewTypeIncident;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Schemas\TypeIncidentForm;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Tables\TypesIncidentTable;
use AcMarche\StreetWatch\Models\TypeIncident;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class TypeIncidentResource extends Resource
{
    #[Override]
    protected static ?string $model = TypeIncident::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Travail de rue';

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = "Types d'incident";

    #[Override]
    protected static ?string $modelLabel = "type d'incident";

    #[Override]
    protected static ?string $pluralModelLabel = "types d'incident";

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TypeIncidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypesIncidentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTypesIncident::route('/'),
            'create' => CreateTypeIncident::route('/create'),
            'view' => ViewTypeIncident::route('/{record}'),
            'edit' => EditTypeIncident::route('/{record}/edit'),
        ];
    }
}
