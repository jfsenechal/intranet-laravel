<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Connection('maria-hrm')]
#[Fillable([
    'name',
    'description',
    'employer_id',
])]
#[Table(name: 'pay_scales')]
final class PayScale extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

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
            ->groupBy(fn (PayScale $payScale): string => $payScale->employer?->name ?? 'Sans employeur')
            ->map(fn ($group) => $group->mapWithKeys(fn (PayScale $payScale): array => [
                $payScale->id => '-- '.$payScale->name,
            ])->all())
            ->all();
    }

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * @return HasMany<Employee>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * @return HasMany<Contract>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
