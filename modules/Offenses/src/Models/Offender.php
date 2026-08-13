<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Connection('maria-offenses')]
#[Fillable([
    'slug',
    'last_name',
    'first_name',
    'birth_date',
    'street',
    'postal_code',
    'city',
    'user_add',
])]
final class Offender extends Model
{
    use HasUserAdd;

    public function offenses(): HasMany
    {
        return $this->hasMany(Offense::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::creating(function (self $offender): void {
            if (blank($offender->slug)) {
                $offender->slug = self::uniqueSlug($offender);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The legacy `slug` column is a unique NOT NULL key with no default and the
     * form does not expose it, so it is derived from the name the way the
     * previous application did ("nom prenom" joined by underscores), with a
     * numeric suffix when that slug is already taken. It is capped well under
     * the column's 70 characters to leave room for that suffix.
     */
    private static function uniqueSlug(self $offender): string
    {
        $base = Str::slug(mb_trim($offender->last_name.' '.$offender->first_name), '_');
        $base = $base === '' ? 'contrevenant' : mb_substr($base, 0, 60);

        $slug = $base;
        $suffix = 0;

        while (self::slugTaken($slug, $offender)) {
            $slug = $base.'_'.++$suffix;
        }

        return $slug;
    }

    private static function slugTaken(string $slug, self $offender): bool
    {
        return self::query()
            ->where('slug', $slug)
            ->when($offender->exists, fn (Builder $query) => $query->whereKeyNot($offender->getKey()))
            ->exists();
    }
}
