<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\ServiceFactory;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Connection('maria-hrm')]
#[Fillable([
    'name',
    'slug',
    'abbreviation',
    'direction_id',
    'employer_id',
    'address',
    'postal_code',
    'city',
    'email',
    'phone',
    'gsm',
    'notes',
    'user_add',
])]
#[Table(name: 'services')]
#[UseFactory(ServiceFactory::class)]
final class Service extends Model
{
    use HasFactory;
    use HasSlug;
    use HasUserAdd;

    /**
     * @return array<string, array<int, string>>
     */
    public static function groupedSelectOptions(): array
    {
        return self::query()
            ->with('employer')
            ->orderBy('employer_id')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Service $service): string => $service->employer?->name ?? 'Sans employeur')
            ->map(fn ($group) => $group->mapWithKeys(fn (Service $service): array => [
                $service->id => '-- '.$service->name,
            ])->all())
            ->all();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['name'])
            ->saveSlugsTo('slug');
    }

    /**
     * @return BelongsTo<Direction>
     */
    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * @return HasMany<Contract>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * @return HasMany<Operator>
     */
    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }
}
