<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Models;

use AcMarche\ActivityManager\Database\Factories\CoursFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CoursFactory::class)]
#[Connection('maria-activity-manager')]
#[Table(name: 'cours')]
#[Fillable([
    'nom',
    'date_debut',
    'date_fin',
    'activite_id',
])]
final class Cours extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Activite, $this>
     */
    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class, 'activite_id');
    }

    /**
     * @return HasMany<DatesCours, $this>
     */
    public function datesCours(): HasMany
    {
        return $this->hasMany(DatesCours::class, 'cours_id');
    }

    /**
     * @return BelongsToMany<Membre, $this>
     */
    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Membre::class, 'inscription', 'cours_id', 'membre_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }
}
