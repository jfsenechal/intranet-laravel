<?php

namespace AcMarche\Security\Models;

use AcMarche\Security\Database\Factories\TabFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// https://github.com/lukas-frey/filament-icon-picker
#[UseFactory(TabFactory::class)]
final class Tab extends Model
{
    use HasFactory;

    protected $connection = 'mariadb';

    protected $fillable = [
        'name',
        'icon',
    ];

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
