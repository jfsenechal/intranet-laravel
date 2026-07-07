<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Models;

use AcMarche\CpasLibrary\Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Override;

#[UseFactory(TagFactory::class)]
#[Connection('maria-cpas-library')]
#[Fillable([
    'name',
    'slug',
])]
final class Tag extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    /**
     * @return BelongsToMany<Fiche, $this>
     */
    public function fiches(): BelongsToMany
    {
        return $this->belongsToMany(Fiche::class, 'fiche_tag', 'tag_id', 'fiche_id');
    }
}
