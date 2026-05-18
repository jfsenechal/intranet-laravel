<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\Incidents;

use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\CreateIncident;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\EditIncident;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\ListIncidents;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\ViewIncident;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Schemas\IncidentForm;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Tables\IncidentsTable;
use AcMarche\StreetWatch\Models\Incident;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class IncidentResource extends Resource
{
    #[Override]
    protected static ?string $model = Incident::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Travail de rue';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Incidents';

    #[Override]
    protected static ?string $modelLabel = 'incident';

    #[Override]
    protected static ?string $pluralModelLabel = 'incidents';

    #[Override]
    protected static ?string $recordTitleAttribute = 'object';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'place',
            'object',
            'description',
            'response',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return IncidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncidentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncidents::route('/'),
            'create' => CreateIncident::route('/create'),
            'view' => ViewIncident::route('/{record}'),
            'edit' => EditIncident::route('/{record}/edit'),
        ];
    }
}
