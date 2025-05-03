<?php

namespace AcMarche\Security\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

//https://github.com/lukas-frey/filament-icon-picker
class Module extends Model
{
    protected $connection = 'mariadb';

    protected $fillable = [
        'name',
        'url',
        'description',
        'extern',
        'is_public',
        'is_external',
        'icon',
        'color',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_external' => 'boolean',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }
}
