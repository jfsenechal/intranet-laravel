<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules;

use AcMarche\ActivityManager\Filament\Resources\Activities\ActivityResource;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\CreateSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\EditSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ViewSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers\MembersRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Schemas\ScheduleForm;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Tables\SchedulesTable;
use AcMarche\ActivityManager\Models\Schedule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class SchedulesResource extends Resource
{
    #[Override]
    protected static ?string $model = Schedule::class;

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

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

    /**
     * Deleting a schedule also deletes its registrations and its sessions, so the confirmation
     * modal has to say how much goes away with it. The record is null once the deletion has run
     * and the modal is re-evaluated.
     */
    public static function deleteModalDescription(?Schedule $schedule): string
    {
        if (! $schedule instanceof Schedule) {
            return 'Cette action est irréversible.';
        }

        $parts = [];

        $registrationsCount = $schedule->members()->count();
        if ($registrationsCount > 0) {
            $parts[] = $registrationsCount.' inscription'.($registrationsCount > 1 ? 's' : '');
        }

        $sessionsCount = $schedule->activitySchedules()->count();
        if ($sessionsCount > 0) {
            $parts[] = $sessionsCount.' séance'.($sessionsCount > 1 ? 's' : '');
        }

        if ($parts === []) {
            return 'Cette action est irréversible.';
        }

        return 'Ce cours sera supprimé avec '.implode(' et ', $parts).'. Cette action est irréversible.';
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return ActivityResource::getUrl('index', [], $isAbsolute, $panel, $tenant);
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateSchedule::route('/create'),
            'view' => ViewSchedule::route('/{record}'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }
}
