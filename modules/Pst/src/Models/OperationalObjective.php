<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Database\Factories\OperationalObjectiveFactory;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Models\Scopes\HasDepartmentScope;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

#[UseFactory(OperationalObjectiveFactory::class)]
#[Connection('maria-pst')]
#[Fillable([
    'name',
    'position',
    'strategic_objective_id',
    'department',
    'scope',
])]
final class OperationalObjective extends Model
{
    use HasDepartmentScope, HasFactory, Notifiable, Searchable;

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'department' => $this->department,
            'scope' => $this->scope,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'pst_operational_objectives_index';
    }

    public function isInternal(): bool
    {
        return $this->scope === ActionScopeEnum::INTERNAL;
    }

    /**
     * Get the strategic objective that owns the operational objective.
     */
    public function strategicObjective(): BelongsTo
    {
        return $this->belongsTo(StrategicObjective::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    /**
     * @return HasMany<Action>
     */
    public function actionsForDepartment(): HasMany
    {
        return $this->actions()->forSelectedDepartment();
    }

    protected static function booted(): void
    {
        self::saving(function (OperationalObjective $model): void {
            if ($model->scope === ActionScopeEnum::INTERNAL) {
                $model->department = null;
            }
        });
    }

    /**
     * @return array<string, class-string>
     */
    protected function casts(): array
    {
        return [
            'scope' => ActionScopeEnum::class,
            'department' => DepartmentEnum::class,
        ];
    }
}
