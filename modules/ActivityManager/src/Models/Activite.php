<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Models;

use AcMarche\ActivityManager\Database\Factories\ActiviteFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(ActiviteFactory::class)]
#[Connection('maria-activity-manager')]
#[Table(name: 'activite')]
#[Fillable([
    'nom',
    'description',
])]
final class Activite extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return HasMany<Cours, $this>
     */
    public function cours(): HasMany
    {
        return $this->hasMany(Cours::class, 'activite_id');
    }
}
