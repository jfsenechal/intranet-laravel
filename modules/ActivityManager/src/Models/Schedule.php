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
final class Schedule extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activite_id');
    }

    /**
     * @return HasMany<SchedulesActivity, $this>
     */
    public function datesCours(): HasMany
    {
        return $this->hasMany(SchedulesActivity::class, 'cours_id');
    }

    /**
     * @return BelongsToMany<Member, $this>
     */
    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'inscription', 'cours_id', 'membre_id');
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
