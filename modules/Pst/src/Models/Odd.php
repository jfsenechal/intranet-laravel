<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\Pst\Database\Factories\OddFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

/**
 * Objectif de développement durable
 */
#[UseFactory(OddFactory::class)]
#[Connection('maria-pst')]
#[Fillable([
    'name',
    'icon',
    'color',
    'description',
    'position',
])]
final class Odd extends Model
{
    use HasFactory, Searchable;

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
            'description' => $this->description,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'pst_odds_index';
    }

    /**
     * @return BelongsToMany<Action>
     */
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class);
    }

    /**
     * @return BelongsToMany<Action>
     */
    public function actionsForDepartment(): BelongsToMany
    {
        return $this->actions()->forSelectedDepartment();
    }
}
