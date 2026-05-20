<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Models;

use AcMarche\SportsActivities\Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(GroupFactory::class)]
#[Connection('maria-rescam')]
#[Fillable([
    'activity_id',
    'day',
    'time',
    'location',
    'age',
    'price',
    'description',
    'comment',
    'user',
])]
final class Group extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
    ];

    public function name(): string
    {
        return 'Groupe '.$this->day.' '.$this->time.' '.$this->age;
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
