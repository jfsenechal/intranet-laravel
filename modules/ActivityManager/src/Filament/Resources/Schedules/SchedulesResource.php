<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules;

use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\CreateSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\EditSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ListSchedules;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ViewSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers\MembersRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers\SchedulesActivityRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Schemas\ScheduleForm;
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
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SchedulesActivityRelationManager::class,
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'view' => ViewSchedule::route('/{record}'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }
}
