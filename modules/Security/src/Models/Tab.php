<?php

declare(strict_types=1);

namespace AcMarche\Security\Models;

use AcMarche\Security\Database\Factories\TabFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

// https://github.com/lukas-frey/filament-icon-picker
#[UseFactory(TabFactory::class)]
#[Fillable([
    'name',
    'icon',
])]
final class Tab extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    #[Override]
    protected $casts = [
    ];

    /**
     * @return HasMany<Module>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'tab_id');
    }
}
