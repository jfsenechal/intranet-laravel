<?php

namespace AcMarche\Security\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,     // Final model we want to access
            Role::class,     // Intermediate model
            'module_id',     // Foreign key on Role table (intermediate table)
            'id',            // Foreign key on User table (referenced in role_user pivot)
            'id',            // Local key on Module table
            'id'             // Local key on Role table
        );
    }

}
