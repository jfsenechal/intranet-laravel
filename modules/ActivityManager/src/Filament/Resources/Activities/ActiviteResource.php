<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activities;

use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\CreateActivity;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\EditActivity;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\ListActivities;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\ViewActivity;
use AcMarche\ActivityManager\Filament\Resources\Activities\RelationManagers\SchedulesRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Activities\Schemas\ActivityForm;
use AcMarche\ActivityManager\Filament\Resources\Activities\Tables\ActivitiesTable;
use AcMarche\ActivityManager\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ActivityResource extends Resource
{
    #[Override]
    protected static ?string $model = Activity::class;

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
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'description',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'view' => ViewActivity::route('/{record}'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }
}
