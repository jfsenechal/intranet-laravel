<?php

namespace AcMarche\Security\Models;

use AcMarche\Security\Database\Factories\ModuleFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// https://github.com/lukas-frey/filament-icon-picker
#[UseFactory(ModuleFactory::class)]
final class Module extends Model
{
    use HasFactory;

    public $timestamps = false;

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

    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return BelongsTo<Tab>
     */
    public function tab(): BelongsTo
    {
        return $this->belongsTo(Tab::class, 'tab_id');
    }

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory()
    {
        return ModuleFactory::new();
    }
}
