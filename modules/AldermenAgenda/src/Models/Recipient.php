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
#[Fillable(['slug', 'last_name', 'first_name', 'email', 'ics', 'token'])]
final class Recipient extends Model
{
    use HasFactory;
    use HasSlug;

    protected $table = 'agenda_echevin_recipients';

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['last_name', 'first_name'])
            ->saveSlugsTo('slug');
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function __toString(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

    protected function casts(): array
    {
        return [
            'ics' => 'boolean',
        ];
    }
}
