<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Models;

use AcMarche\ActivityManager\Database\Factories\ActiviteFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(ActiviteFactory::class)]
#[Connection('maria-activity-manager')]
#[Table(name: 'activities')]
#[Fillable([
    'name',
    'description',
])]
final class Activity extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return HasMany<Schedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'activity_id');
    }

    protected static function booted(): void
    {
        /**
         * `schedules.activity_id` is an ON DELETE RESTRICT foreign key, and deleting an activity
         * would silently take every schedule and every registration with it, so an activity that
         * still has schedules cannot be deleted. Its schedules have to be deleted one by one first.
         */
        self::deleting(fn (self $activity): bool => ! $activity->schedules()->exists());
    }
}
