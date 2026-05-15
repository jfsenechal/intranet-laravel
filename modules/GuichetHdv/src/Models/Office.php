<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Models;

use AcMarche\GuichetHdv\Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-guichet')]
#[Fillable(['name', 'service'])]
final class Office extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return HasMany<Ticket>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    protected static function newFactory(): OfficeFactory
    {
        return OfficeFactory::new();
    }
}
