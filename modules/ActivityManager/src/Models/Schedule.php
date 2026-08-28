<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Models;

use AcMarche\ActivityManager\Database\Factories\CoursFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CoursFactory::class)]
#[Connection('maria-activity-manager')]
#[Table(name: 'schedules')]
#[Fillable([
    'name',
    'start_date',
    'end_date',
    'activity_id',
])]
final class Schedule extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * @return HasMany<SchedulesActivity, $this>
     */
    public function activitySchedules(): HasMany
    {
        return $this->hasMany(SchedulesActivity::class, 'schedule_id');
    }

    /**
     * @return BelongsToMany<Member, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'registrations', 'schedule_id', 'member_id');
    }

    protected static function booted(): void
    {
        /**
         * `registrations.schedule_id` and `activity_schedules.schedule_id` are ON DELETE RESTRICT
         * foreign keys in the legacy database, so the registrations and the sessions have to go first.
         */
        self::deleting(function (self $schedule): void {
            $schedule->members()->detach();
            $schedule->activitySchedules()->delete();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
