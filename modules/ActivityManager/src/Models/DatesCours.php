<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Models;

use AcMarche\ActivityManager\Database\Factories\DatesCoursFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(DatesCoursFactory::class)]
#[Connection('maria-activity-manager')]
#[Table(name: 'dates_cours')]
#[Fillable([
    'cours_id',
    'remarque',
    'jour',
])]
final class DatesCours extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Cours, $this>
     */
    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class, 'cours_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jour' => 'datetime',
        ];
    }
}
