<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\EmployerFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property int|null $parent_id
 * @property-read Employer|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employer> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Direction> $directions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $employees
 */
#[Connection('maria-hrm')]
#[Fillable([
    'name',
    'slug',
    'parent_id',
])]
#[Table(name: 'employers')]
#[UseFactory(EmployerFactory::class)]
final class Employer extends Model
{
    use HasFactory;

    /**
     * @return array<int>
     */
    public static function descendantsAndSelfIds(int $id): array
    {
        return self::query()
            ->where('id', $id)
            ->orWhere('parent_id', $id)
            ->pluck('id')
            ->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function groupedSelectOptions(): array
    {
        $employers = self::query()->orderBy('name')->get();
        $childrenByParent = $employers->whereNotNull('parent_id')->groupBy('parent_id');

        $options = [];
        foreach ($employers->whereNull('parent_id') as $parent) {
            $group = [$parent->id => $parent->name];
            foreach ($childrenByParent->get($parent->id, []) as $child) {
                $group[$child->id] = '-- '.$child->name;
            }
            $options[$parent->name] = $group;
        }

        return $options;
    }

    /**
     * @return BelongsTo<Employer>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Employer>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Direction>
     */
    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class, 'employer_id');
    }

    /**
     * Employees holding a contract with this employer, past or present. An
     * employee with several contracts appears once per contract.
     *
     * @return HasManyThrough<Employee, Contract>
     */
    public function employees(): HasManyThrough
    {
        return $this->hasManyThrough(Employee::class, Contract::class, 'employer_id', 'id', 'id', 'employee_id');
    }
}
