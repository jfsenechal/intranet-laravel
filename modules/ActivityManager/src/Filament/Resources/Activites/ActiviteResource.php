<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activites;

use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\CreateActivite;
use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\EditActivite;
use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\ListActivites;
use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\ViewActivite;
use AcMarche\ActivityManager\Filament\Resources\Activites\RelationManagers\CoursRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Activites\Schemas\ActiviteForm;
use AcMarche\ActivityManager\Filament\Resources\Activites\Tables\ActivitesTable;
use AcMarche\ActivityManager\Models\Activite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ActiviteResource extends Resource
{
    #[Override]
    protected static ?string $model = Activite::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Activités';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Activités';

    #[Override]
    protected static ?string $modelLabel = 'activité';

    #[Override]
    protected static ?string $pluralModelLabel = 'activités';

    #[Override]
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nom',
            'description',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ActiviteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CoursRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivites::route('/'),
            'create' => CreateActivite::route('/create'),
            'view' => ViewActivite::route('/{record}'),
            'edit' => EditActivite::route('/{record}/edit'),
        ];
    }
}
