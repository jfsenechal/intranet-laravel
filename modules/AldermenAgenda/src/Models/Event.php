<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Connection('maria-aldermen-agenda')]
#[Fillable([
    'slug',
    'event_type',
    'title',
    'description',
    'start_at',
    'end_at',
    'reminder_at',
    'is_walk',
    'organizer',
    'location',
    'representative',
    'sent',
    'file1_name',
    'file2_name',
])]
final class Event extends Model
{
    use HasFactory;
    use HasSlug;

    protected $table = 'agenda_echevin_events';

    public function __toString(): string
    {
        return $this->title;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['title'])
            ->saveSlugsTo('slug')
            ->allowSlugReuse();
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'reminder_at' => 'datetime',
            'is_walk' => 'boolean',
            'sent' => 'boolean',
        ];
    }
}
