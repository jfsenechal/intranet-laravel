<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Models;

use AcMarche\ActivityManager\Database\Factories\MembreFactory;
use AcMarche\ActivityManager\Enums\CiviliteEnum;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UseFactory(MembreFactory::class)]
#[Connection('maria-activity-manager')]
#[Table(name: 'membre')]
#[Fillable([
    'civilite',
    'nom',
    'prenom',
    'rue',
    'numero',
    'codepostal',
    'localite',
    'gsm',
    'telephone',
    'email',
    'enabled',
    'remarque',
    'inscrit_le',
])]
final class Member extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsToMany<Schedule, $this>
     */
    public function cours(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'inscription', 'membre_id', 'cours_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'civilite' => CiviliteEnum::class,
            'enabled' => 'boolean',
            'inscrit_le' => 'date',
        ];
    }
}
