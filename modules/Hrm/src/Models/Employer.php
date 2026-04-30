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

#[Connection('maria-hrm')]
#[Fillable([
    'name',
    'slug',
    'parent_id',
])]
#[Table(name: 'employers')]
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
}
