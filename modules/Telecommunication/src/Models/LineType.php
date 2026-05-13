<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Models;

use AcMarche\Telecommunication\Database\Factories\LineTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(LineTypeFactory::class)]
#[Connection('maria-telecommunication')]
#[Fillable([
    'slug',
    'name',
])]
final class LineType extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return HasMany<Telephone, $this>
     */
    public function telephones(): HasMany
    {
        return $this->hasMany(Telephone::class);
    }
}
