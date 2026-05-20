<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Models;

use AcMarche\SportsActivities\Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(ActivityFactory::class)]
#[Connection('maria-rescam')]
#[Fillable([
    'name',
    'description',
    'user',
    'archived',
])]
final class Activity extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'archived' => 'boolean',
    ];

    /**
     * @return HasMany<Group, $this>
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
