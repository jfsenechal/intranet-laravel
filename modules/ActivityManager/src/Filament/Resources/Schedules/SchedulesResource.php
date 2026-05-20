<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules;

use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\CreateSchedules;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\EditSchedules;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ListSchedules;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ViewSchedules;
use AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers\DatesSchedulesRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers\MembersRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Schemas\SchedulesForm;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Tables\SchedulesTable;
use AcMarche\ActivityManager\Models\Schedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class SchedulesResource extends Resource
{
    #[Override]
    protected static ?string $model = Schedule::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Activités';

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Schedules';

    #[Override]
    protected static ?string $modelLabel = 'cours';

    #[Override]
    protected static ?string $pluralModelLabel = 'cours';

    #[Override]
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nom',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return SchedulesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DatesSchedulesRelationManager::class,
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedules::route('/create'),
            'view' => ViewSchedules::route('/{record}'),
            'edit' => EditSchedules::route('/{record}/edit'),
        ];
    }
}
