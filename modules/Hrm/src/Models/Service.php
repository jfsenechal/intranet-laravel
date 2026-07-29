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

/**
 * @property int $id
 * @property string $name
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property string $user_add
 * @property int|null $direction_id
 * @property string|null $abbreviation
 * @property int|null $employer_id
 * @property int|null $postal_code
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $gsm
 * @property string|null $address
 * @property string|null $city
 * @property string|null $notes
 * @property-read Direction|null $direction
 * @property-read Employer|null $employer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Contract> $contracts
 */
#[Connection('maria-hrm')]
#[Fillable([
    'name',
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

    protected static function booted(): void
    {
        self::bootHasUser();
    }
}
